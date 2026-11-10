# Convenção 08 — Testes: PHPUnit dockerizado com Builders/Directors

- **Stack**: PHPUnit ^10.5 (`composer.json:33`), sem `phpunit.xml`, sem `composer scripts`, sem Makefile; roda dockerizada por `bash tests/run.sh` (`docker compose run mapas phpunit /var/www/tests`); shell interativo `bash tests/bash.sh` + `pu <path>` dentro do container. **CI não roda testes** (só build de imagem).
- **Estrutura** (`tests/src/`): `Abstract/TestCase` (transação+rollback; `processJobs()`/`processPCache()`), `Abstract/Builder` (fluent, `save()` persiste + fila pcache), `Directors/` (cenários de negócio compostos de builders; desabilitam access control pontualmente), `Traits/` (wiring por `__init*`), `Factories/RequestFactory` (PSR-7 via `createUrl` — roteador real), `Enums/`, `Interfaces/`.
- **Padrão de escrita**: herdar TestCase → usar trait Director/Builder → `reset()->fillRequiredProperties()->save()`; assertions HTTP in-process (`assertStatus200/401/403`); `processJobs('2100-01-01 00:00')` para drenar fila; SQL direto para campos write-protected.
- **Nomenclatura**: `*Test.php` por arquivo, `test*` por método; builders por entidade (`OpportunityBuilder` com sub-builders de fase: `firstPhase()->setRegistrationPeriod(new Open)->done()->addEvaluationPhase(...)`).

**Modelo canônico**: `tests/src/EvaluationConsolidationTest.php`. Ver RB7 para o mapa de cobertura.
