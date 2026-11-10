# RB7 — Testes: rodar, iterar e interpretar

**Gatilho**: escrever/rodar testes; teste que depende de jobs/permissões "flakando".

## Comandos (verificados)
- **Suíte completa**: `bash tests/run.sh [-b]` → `docker compose run --service-ports mapas phpunit /var/www/tests` (`tests/run.sh`; `-b` rebuilda a imagem).
- **Shell interativo do ambiente de testes**: `bash tests/bash.sh [-b]` — dentro do container, `/bin/pu <caminho-relativo>` roda um arquivo sem isolamento de processo (mais rápido para iteração; `tests/docker/pu.sh`).
- **Rodar um arquivo via run.sh**: `bash tests/run.sh src/EvaluationConsolidationTest.php` (o argumento é sufixado a `/var/www/tests`).
- **NÃO EXISTEM**: `composer test`, `phpunit.xml`, Makefile (verificação por ausência — QA R1 §5.2). **O CI NÃO roda testes** (`ci.yml` tem só o job docker de build/push).

## Padrões obrigatórios do TestCase
1. Herdar `Tests\Abstract\TestCase` (transação + rollback por teste; `tests/src/Abstract/TestCase.php:36-62`).
2. Jobs: chamar `$this->processJobs('2100-01-01 00:00')` até drenar (senão job agendado nunca roda).
3. Permissões: `$this->processPCache()` após conceder/alterar (senão o teste lê permissão VELHA da pcache).
4. Dados: builders fluentes (`reset()->fillRequiredProperties()->save()`) + directors para cenários; HTTP in-process via `RequestFactory` (usa `createUrl` — exercita o roteador real).
5. Campos write-protected (`score`, `consolidated_result`): SQL direto (precedente `tests/src/Directors/RegistrationDirector.php:126-135`).

## Onde teste é esperado (mapa de cobertura)
Forte: Opportunities/fases, EvaluationMethod*, Seals/SealExemption, campos de registro, roles/rotas, hooks. **Ausente**: subsite/multi-tenant, auth HTTP real, worker de jobs em si, assets, ApiQuery DSL completa (QA R1 §4.4).

**Ambiente**: `tests/docker-compose.yml` (postgis 16 + seed `tests/db/dump.sql` + mailhog; `TESTING=1`; entrypoint próprio sem crons).
