# 0017. Deploy por imagem Docker única com convergência no boot: migrations two-phase no entrypoint

**Status:** aceito

## Contexto histórico

Desde o "Dockerfile para produção" (2018-07-10, `e36c8419d`/`ad158e966`), o unit of deployment do MapasCulturais é uma **imagem Docker única** versionada no Docker Hub pelo CI (ADR 0016). As instâncias (50+) consomem essa imagem via repositórios "Base Project" externos (`README.md:82-85` — compose de instância fora deste repo); a homologação do próprio projeto consome via Kubernetes (`develop.yml`). Desde 2023, há também deploy de homolog por `kubectl` (2025: `811aa2f4f`, `dc4419c8d`).

O mecanismo interno de evolução de schema — ledger de updates nomeados em `db-updates.php`/`mc-updates.php` registrado na tabela `db_update`, com helpers `__exec`/`__try` e two-phase `DISABLE_PLUGINS` — é documentado no **ADR 0006** (`0006-evolucao-schema-por-ledger-db-updates.md`, domínio de dados); este ADR cobre apenas o **lado deploy**: quando, onde e com qual consequência operacional esse mecanismo é invocado.

## Decisão

**Deploy = boot do container.** Não existe estágio de migration no CI/CD nem job de deploy: o `entrypoint.sh` da imagem converge o banco e o ambiente a cada subida, nesta ordem exata:

1. **Espera bloqueante pelo PostgreSQL** (loop PDO até conectar; `docker/entrypoint.sh:4-22`).
2. **Migrations core (two-phase)**: `sudo -E -u www-data /var/www/scripts/db-update.sh` (`entrypoint.sh:33`) executa o tool **duas vezes** — primeiro com `DISABLE_PLUGINS=1` (só updates do core), depois com plugins carregados (`scripts/db-update.sh:25-28`; `DISABLE_PLUGINS` lido em `src/core/App.php:1204`). Isso permite que updates do core corrijam o schema **antes** de plugins de instância quebrarem o boot.
3. **Migrations de dados**: `mc-db-updates.sh` (`entrypoint.sh:34`) — updates row-based multicore (`-p`, `MC_UPDATES_PROCESSES`).
4. **Gate de versão**: se `version.txt` ≠ `var/private-files/deployment-version` → recompila SASS do tema ativo e regenera proxies Doctrine **fora da imagem** (`entrypoint.sh:36-41`). Ou seja: cada release efetivamente recalcula artefatos derivados no destino, não no build.
5. **`BUILD_ASSETS=1` (opt-in)**: rebuild completo de assets JS no boot para código bind-mountado (`entrypoint.sh:44-49`; default `0` em `dev/docker-compose.yml:48` e `tests/docker-compose.yml:29`).
6. Disparo dos workers (ADR 0015) e `exec "$@"`.

O fluxo de release de código é: `scripts/publish-version.sh` orquestra branch `release/<v>` → grava `version.txt` → merge `--no-ff -Xtheirs` na branch de produção → tag (envs `STABLE_BRANCH`/`PROD_BRANCH`) → o CI da tag publica a imagem semver (`ci.yml:34-36`) → instâncias/boot convergem. Homolog ignora o ritual: `dev/**` → imagem + `kubectl set image` + `rollout restart` (`develop.yml:82-84`).

**Rollback de aplicação** = reverter a imagem/pod para a tag anterior (sem etapa de schema: o ledger é append-only e updates são idempotentes por design — ver ADR 0006; não há down-migration).

## Alternativas consideradas/descartadas

- **Migration como job/stage de CI antes do deploy**: descartado de fato — exigiria credenciais de produção no CI e coordenação por instância (50+), o oposto do modelo "instância sobe e converge sozinha".
- **Migrations versionadas com down (Doctrine Migrations/Flyway)**: ver ADR 0006 (alternativa analisada lá); o lado deploy herda a consequência: rollback de schema não existe.
- **Init containers (Kubernetes) para migrations**: nunca adotado; o deploy de homolog usa um único deployment sem initContainer visível no repo (manifests fora do repo — lacuna declarada; a inferência vem de `develop.yml:82-84`, que só faz `set image`/`rollout restart`).

## Consequências

**Positivas**
- **Convergência automática por instância**: qualquer instância, em qualquer versão anterior, alcança o estado atual apenas subindo o container novo — sem orquestrador de deploy central saber nada de schema. É o que torna viável operar 50+ instalações heterogêneas com um time pequeno.
- Two-phase `DISABLE_PLUGINS` degrada graciosamente: update do core pode criar a coluna que um plugin antigo precisa antes que o plugin carregue.
- Idempotência dos updates faz restart/redeploy serem seguros por construção (retry natural: update que lança exceção não é gravado no ledger e re-executa no próximo boot — `App.php:5475-5477`).

**Negativas**
- **Boot bloqueante e potencialmente longo**: em release com updates pesados (ex.: `'recreate pcache'` reprocessa todas as entidades — `src/mc-updates.php:17-24`), o container fica indisponível até convergir; com `docker stop $(docker ps -q)` no fluxo legado (`scripts/deploy-ref.sh:100-101`), a instância cai inteira durante a janela.
- **Falha de update = instância fora do ar**: um update não-idempotente quebra o boot de toda instância que sobe o container (custo de erro que justifica a skill de migrations — DevOps R2 §4.3, DBA R1 §10). A exceção é capturada e o boot continua com o update **não gravado** (`App.php:5475-5477`) — o erro pode passar despercebido e repetir a cada boot.
- **Invalidação total de cache a cada update novo** (`$this->cache->deleteAll()`, `App.php:5482-5485`): primeiro boot pós-deploy paga cache frio em toda a instância.
- Artefatos derivados (SASS, proxies Doctrine) dependem do estado do volume de destino e do gate de versão (`entrypoint.sh:36-41`) — imagem não é 100% autossuficiente para temas de instância.
- Rollback de aplicação com schema novo permanece possível apenas porque o código antigo tolera colunas extras; não há garantia no mecanismo, apenas na disciplina de updates aditivos.

**Neutras**
- `kubectl rollout restart deployment redis` junto ao deploy de homolog (`develop.yml:83`) descarta cache de homolog a cada deploy — coerente com o `deleteAll`, mas vale registro operacional.

## Evidência

- `docker/entrypoint.sh:4-22` (wait), `:33-34` (two-phase + mc), `:36-41` (gate de versão), `:44-49` (BUILD_ASSETS), `:65`.
- `scripts/db-update.sh:25-28` (duas passadas); `src/core/App.php:1204` (`DISABLE_PLUGINS`), `:525-527` e `:5443-5488` (`_dbUpdates`, `deleteAll`), `src/tools/apply-updates.php:5`.
- `scripts/mc-db-updates.sh:28-66`; `src/tools/apply-multicore-db-update.php:195-197` (só processo 1 grava ledger).
- `scripts/publish-version.sh:56-84`; `.github/workflows/ci.yml:32-36`; `.github/workflows/develop.yml:74-84`; `scripts/deploy-ref.sh:96-106` (fluxo legado MINC).
- `version.txt` (marker de versão); `dev/docker-compose.yml:48` (`BUILD_ASSETS=0`).
- Cross-verification: DevOps R1 §3/§6 + DBA R1 §5.2/§5.6 (mesmo caminho traçado independentemente, zero divergência — R2 §3.2) + QA R1 #14.

**Referências:** ADR 0015 (runtime multi-papel que este boot inicializa); ADR 0016 (como a imagem chega ao registry/homolog); ADR 0006 — `0006-evolucao-schema-por-ledger-db-updates.md` (mecanismo interno das migrations). **Fronteira com ADR 0014** (pipeline duplo de assets): 0014 é dono do mecanismo de assets (AssetManager publish/enqueue/minify); este ADR documenta apenas as etapas de boot que o acionam (gate de versão + `BUILD_ASSETS`, `entrypoint.sh:36-49`) — âncoras parcialmente coincidentes, propósitos distintos; não fundir.
