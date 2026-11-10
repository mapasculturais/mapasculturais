# 0016. CI build-only: a imagem Docker é o único gate — a suíte de testes não bloqueia merge nem deploy

**Status:** aceito

## Contexto histórico

O repositório teve CI de testes no passado: `.travis.yml` rodava PHPUnit em PHP 7.2 com PostgreSQL 9.6/PostGIS 2.3 (`.travis.yml:1-15`). Esse pipeline está **morto**: referencia `./scripts/run-tests.sh`, que não existe no repo (apenas `scripts/run-tests-docker.sh`), e paths da estrutura pré-Docker (`src/protected` — `.travis.yml:19`) — vestígio não removido.

A mudança para GitHub Actions veio em 2023-08-02 ("Adiciona configuração de pipeline no github actions", `fe6e04518`; ajuste para buildar develop em `a351324e6`, 2023-08-16). Nenhum workflow criado desde então executa PHPUnit: a suíte atual (`tests/`, reescrita em 2025 — QA R1 §4.1) roda apenas localmente por invocação explícita do operador.

## Decisão

O pipeline de CI valida **apenas o build da imagem Docker**:

- `ci.yml`: único job `docker` — build multi-arch (QEMU/Buildx) e push para `docker.io/$DOCKERHUB_ORGANIZATION/$DOCKERHUB_IMAGE` em push para `master`/`develop`/`saas` e tags `v*.*.*`; PRs para `develop` buildam sem push (`.github/workflows/ci.yml:3-68`).
- `develop.yml`: branches `dev/**` buildam imagem com tag derivada e **deployam em homologação** via `kubectl set image deployment/mapasculturais ... -n mapas-homolog` + `rollout restart` (`develop.yml:74-84`, introduzido 2025-02/03 — `811aa2f4f`, `dc4419c8d`).
- `rc.yml`: branches `release/*` buildam tag `*-RC` sem deploy (`rc.yml:15-54`).

Em consequência, o "gate" de qualidade de merge/deploy é o build da imagem + revisão humana. A suíte de testes existe, é dockerizada e é o caminho oficial documentado (`bash tests/run.sh`, `tests/run.sh:33` → `phpunit /var/www/tests`), mas **não roda em nenhum workflow**.

## Alternativas consideradas/descartadas

- **Job de testes no CI** (como no Travis original): não foi portado para GitHub Actions. Não há evidência no repo de decisão registrada — o estado é de fato ("ninguém portou"), não de jure.
- **Testes como estágio do build da imagem**: possível (a imagem de testes já inclui as dependências dev via `COMPOSER_ARGS=` vazio, `tests/docker-compose.yml:6-7`), mas exigiria serviço de PostGIS no runner — infraestrutura nunca adicionada aos workflows.
- **Gates adicionais (lint, análise estática, scan de imagem)**: também ausentes — nenhum workflow roda phpstan/phpcs/trivy/sbom.

## Consequências

**Positivas**
- Pipeline rápido e barato (cache GHA, `ci.yml:67-68`); o build da imagem de fato valida sintaxe PHP a nível de `composer install` e compilação de assets (`Dockerfile:83-96`).
- A suíte local é fiel ao ambiente (mesmo Dockerfile, PostGIS 16 real com dump de seed, mailhog — `tests/docker-compose.yml`), sem custo de manutenção de runner.

**Negativas**
- **Regressões funcionais entram por PR sem sinal automático**: a confiança recae inteiramente na revisão humana. A suíte cobre áreas críticas (avaliações, distribuição, cotas, permissões — QA R1 §4.4) e ainda assim não bloqueia nada.
- Deploy de homologação (`develop.yml`) ocorre direto após o build — um PR com testes vermelhos é publicável em `dev/*` sem qualquer barreira.
- Tags de branch são **mutáveis** (re-push regrava a tag no Docker Hub — `ci.yml:33` `type=ref,event=branch`), em contraste com as semver; rollback por tag de branch não é garantidamente reproduzível.

**Neutras**
- A ausência de `composer scripts` (`composer.json` não tem a chave — QA R1 §5.2) e de `phpunit.xml` (glob vazio) indica que a suíte nunca foi desenhada para invocação por CI sem o wrapper docker.

## Evidência

- `.github/workflows/ci.yml:3-68` (job único `docker`; sem step de teste); `develop.yml:18-84`; `rc.yml:15-54`.
- `.travis.yml:25-26` (script `./scripts/run-tests.sh` inexistente — CI morto verificado).
- `tests/run.sh:33-37`; `tests/docker/phpunit.sh`; `composer.json` (sem `scripts`).
- Cross-verification (4 fontes independentes): DevOps R1 §9 + QA R1 §5.3/#13 + Onboarding R1 (tabela workflows) + PM R1 R11/RNF — nenhuma análise encontrou workflow que execute testes.

**Nota de imutabilidade:** este ADR descreve o estado aceito (em vigor). Qualquer mudança — adicionar job de testes, gates de scan — deve nascer como novo ADR referenciando este.
