---
name: mapas-culturais-tdd-builders
description: Como escrever e rodar testes no MapasCulturais com a arquitetura Builder/Director/trait de tests/src — TestCase com transação+rollback, processJobs/processPCache obrigatórios e os comandos reais dockerizados.
---
# Skill: tdd-builders — testes com Builders/Directors em tests/ (PHPUnit dockerizado)

## Description

Como escrever e rodar testes no MapasCulturais: a arquitetura Builder/Director/trait da suíte `tests/src/`, o `TestCase` com transação+rollback, os utilitários obrigatórios (`processJobs`, `processPCache`) e os comandos reais. Use antes de escrever qualquer teste novo.

## Receita

### 1. Comandos reais (verificados)

```bash
bash tests/run.sh                      # suíte inteira (docker compose run mapas phpunit /var/www/tests)
bash tests/bash.sh                     # shell interativo no container de testes  ← caminho VERIFICADO p/ arquivo único
  /bin/pu src/MeuTest.php              # dentro do container: arquivo único SEM --process-isolation (iteração rápida)
  /bin/phpunit ...                     # com --process-isolation (default do wrapper)
```

- Fonte: `tests/run.sh:33-34`, `tests/bash.sh:33-35`, `tests/docker/phpunit.sh:2`, `tests/docker/pu.sh:2`.
- **[HIPÓTESE — não verificado estaticamente]** `bash tests/run.sh src/MeuTest.php` (path como argumento posicional): o `run.sh` repassa `$@` como sufixo de `/var/www/tests`, e PHPUnit 10 com **dois paths posicionais** pode interpretar o segundo como filtro/argumento distinto em vez de sufixo de path — comportamento não verificável sem execução. **Use o caminho verificado para arquivo único: `bash tests/bash.sh` → `/bin/pu <path>`** (alinhado com o RNF-11 do PRD).
- **Não existe** `composer test`, `phpunit.xml` ou CI de testes (`.github/workflows/ci.yml` é build-only — nunca afirme que o CI roda PHPUnit).
- Ambiente: `tests/docker-compose.yml` (postgis 16 + seed `tests/db/dump.sql` + mailhog; `COMPOSER_ARGS=` vazio para deps dev).

### 2. Estrutura do teste (o esqueleto canônico)

```php
namespace Tests;

use Tests\Abstract\TestCase;

class MeuFluxoTest extends TestCase
{
    use \Tests\Traits\UserDirector,          // ganha $this->userDirector
        \Tests\Traits\OpportunityDirector,   // ganha $this->opportunityDirector
        \Tests\Traits\RegistrationDirector;  // ganha $this->registrationDirector

    public function testMeuCenario() {
        $this->userDirector->createUser();                    // admin desliga access control pontualmente
        $opportunity = $this->opportunityDirector->createOpportunity(
            owner: $agent, ownerEntity: $project,
            new \Tests\Builders\PhasePeriods\Open,            // janela temporal da fase
            \Tests\Enums\EvaluationMethods::simple
        );
        $registrations = $this->registrationDirector->createSentRegistrations($opportunity, 3);

        $this->processJobs('2100-01-01 00:00');   // drena a fila de jobs (data futura = tudo due)
        $this->processPCache();                    // drena a fila de pcache

        $this->assertEquals(10, $registrations[0]->status);
    }
}
```

### 3. As três peças (contratos verificados)

- **`Tests\Abstract\TestCase`** (`tests/src/Abstract/TestCase.php`): `setUp` abre **transação** + limpa os 3 caches (`app.cache/mscache/rcache`) + logout (`:36-53`); `tearDown` faz **rollback** (`:55-62`) — o banco não é recriado por teste. `login(User)` seta `auth->authenticatedUser` direto (`:66-71`). `processJobs($as_date)` (`:81-95`) e `processPCache()` (`:97-108`) são obrigatórios em qualquer teste que dependa de jobs/permissões. Asserções HTTP in-process com PSR-7 fabricado (`:125-158`) via `tests/src/Factories/RequestFactory.php` (usa `createUrl` — exerce a URL real do roteador).
- **Builder** (`tests/src/Abstract/Builder.php`): exige `instance` + `reset()`; **`save()` também chama `persistPCachePendingQueue()`** (`:32-38`) — par obrigatório com o rollback para dados+pcache consistentes; `fillRequiredProperties()` abstrato. 24 builders em `tests/src/Builders/` (Opportunity com sub-builders de fase fluentes: `firstPhase()/addEvaluationPhase(EvaluationMethods::x)->setEvaluationPeriod(new ConcurrentEndingAfter)->done()`; Registration setando categoria/faixa/proponentType automaticamente; `PhasePeriods/*` como objetos-período).
- **Director** (`tests/src/Abstract/Director.php` + 10 em `tests/src/Directors/`): monta **cenários** compostos de builders; expostos ao teste como **traits** em `tests/src/Traits/` (17) que instanciam em `__initXxx()`. `RegistrationDirector::createSentRegistrations/createDraftRegistrations` aceitam `$data` com overrides (timestamps como string) e escrevem `score/consolidatedResult` por **SQL direto** (campos write-protected no ORM — `Directors/RegistrationDirector.php:126-135`).

### 4. Convenção `__init*`

TestCase, Builder, Director e as traits executam métodos com prefixo `__init` no construtor (`TestCase.php:23-34`; `Builder.php:24-29`) — é o mecanismo de wiring: `use \Tests\Traits\RegistrationDirector` cria `$this->registrationDirector` pronto.

### 5. Padrões observados (use-os)

- Diretores desabilitam access control pontualmente (desabilita → opera → reabilita — `Directors/UserDirector.php:25-40`); nunca deixe desabilitado vazando entre asserts.
- Teste estático de conteúdo declarativo (templates/metadata) estende `PHPUnit\Framework\TestCase` puro com `file_get_contents` + `assertStringContainsString` (ex.: `OpportunityPhaseDatesVisibilityStaticTest`).
- Mocks prontos: mailer `tests/src/Mailer/TestTransport.php`, captcha `tests/src/Captcha/MockCaptcha.php`.

## Exemplos reais citados (>3)

1. `tests/src/EvaluationConsolidationTest.php:23-48` — o teste-canônico: opportunityBuilder encadeado + evaluation phase + valuers + `redistributeCommitteeRegistrations()` + asserções de consolidação.
2. `tests/src/Directors/RegistrationDirector.php:57-91` — cenário de inscrições enviadas com overrides.
3. `tests/src/Directors/UserDirector.php:20-43` — criação de usuário com roles e o padrão access-control pontual.
4. `tests/src/PhaseRegistrationSyncTest.php`, `tests/src/EvaluationsDistributionTest.php`, `tests/src/OpportunityExecutionPhaseTest.php` — fluxos completos de domínio (sync de fases, distribuição, execução) sobre a mesma arquitetura.
5. `tests/src/RoutesTest.php:105-347` — matriz 401/403 por papel via RequestFactory in-process.

## Armadilhas

- Sem `processJobs`, jobs de fase/distribuição não rodam e o teste vê estado intermediário; sem `processPCache`, permissões recém-criadas não aparecem (fila assíncrona).
- Rollback por transação: dados gravados por **SQL cru de OUTRA conexão** ou por processos externos não são revertidos — mantenha tudo na conexão do app.
- `--process-isolation` (default do `/bin/phpunit`) é necessário pelo estado global do `App`, mas é lento — itere com `/bin/pu`.
- Assert sobre `score/consolidatedResult` escritos pelo ORM falham silenciosamente (write-protected) — use o override SQL do Director.
- 16 arquivos usam `processJobs`; cobertura ausente documentada (subsite, fila como infra, assets, appeal-phase/accountability dedicados) — não assuma que existe teste para o que vai mudar.
