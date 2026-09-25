<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão do gráfico de faixas de nota do relatório (avaliação técnica):
 * - consolidated_result vazio (avaliação em andamento) não derruba a consulta
 * - consolidated_result '@tiebreaker' não derruba a consulta
 * - rótulo de status gravado pelas fases não derruba a consulta
 * - notas válidas continuam contadas na faixa correta
 * - guards de código-fonte (cast protegido no módulo e no controller do CSV)
 */
class ReportsEvaluationStatusBarTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function createTechnicalOpportunity(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->done();

        $evaluation_phase_builder = $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->config()
                    ->addSection('sec1', 'Seção 1')
                    ->addCriterion('cri1', 'sec1', 'Critério 1', 0, 100, 1)
                    ->done()
                ->save();

        return $evaluation_phase_builder->done()->getInstance();
    }

    /**
     * Grava o valor direto na coluna: o setter recalcularia o resultado, e o
     * que interessa aqui é o estado exato que fica persistido em produção.
     */
    private function setConsolidatedResult(array $registrations, string $value): void
    {
        $connection = $this->app->em->getConnection();

        foreach ($registrations as $registration) {
            $connection->executeQuery(
                'UPDATE registration SET consolidated_result = :value WHERE id = :id',
                ['value' => $value, 'id' => $registration->id]
            );
        }
    }

    private function scoreRanges(Opportunity $opportunity): array
    {
        return $this->app->modules['Reports']->registrationsByEvaluationStatusBar($opportunity);
    }

    /**
     * Inscrições sem nota não podem derrubar a consulta nem entrar em faixa.
     */
    private function assertChartIgnoresValue(string $value, string $origin): void
    {
        $opportunity = $this->createTechnicalOpportunity();
        $registrations = $this->registrationDirector->createSentRegistrations($opportunity, 3);

        $this->setConsolidatedResult($registrations, $value);

        $ranges = $this->scoreRanges($opportunity);

        $this->assertCount(
            5,
            $ranges,
            "Certificando que as cinco faixas de nota são devolvidas mesmo com consolidated_result gravado por {$origin}"
        );

        $this->assertSame(
            0,
            array_sum(array_map('intval', $ranges)),
            "Certificando que inscrições sem nota, gravadas por {$origin}, não entram em nenhuma faixa"
        );
    }

    private function projectSrcPath(string $relative): string
    {
        // Local: tests/src → ../../src ; Docker (mount tests/src → /var/www/tests): ../src
        $candidates = [
            dirname(__DIR__, 2) . '/src/' . $relative,
            dirname(__DIR__) . '/src/' . $relative,
            '/var/www/src/' . $relative,
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $candidates[0];
    }

    public function testEmptyConsolidatedResultDoesNotBreakScoreRangeChart(): void
    {
        // EvaluationMethod::getConsolidatedResult grava string vazia enquanto
        // nem todos os avaliadores enviaram a avaliação
        $this->assertChartIgnoresValue('', 'avaliação em andamento');
    }

    public function testTiebreakerConsolidatedResultDoesNotBreakScoreRangeChart(): void
    {
        // EvaluationMethod::getConsolidatedResult grava @tiebreaker quando
        // falta a avaliação de desempate
        $this->assertChartIgnoresValue('@tiebreaker', 'aguardando desempate');
    }

    public function testPhaseStatusLabelDoesNotBreakScoreRangeChart(): void
    {
        // OpportunityPhases::getRegistrationStatusLabels grava o rótulo
        // traduzido do status ao sincronizar a inscrição para a fase seguinte
        $this->assertChartIgnoresValue('Pendente em "Avaliação Técnica"', 'rótulo de status da fase');
    }

    public function testValidScoresAreCountedAlongsideRegistrationsWithoutScore(): void
    {
        $opportunity = $this->createTechnicalOpportunity();

        $without_score = $this->registrationDirector->createSentRegistrations($opportunity, 3);
        $this->setConsolidatedResult($without_score, '');

        $with_score = $this->registrationDirector->createSentRegistrations($opportunity, 2);
        $this->setConsolidatedResult($with_score, '18.00');

        $ranges = $this->scoreRanges($opportunity);

        $this->assertSame(
            2,
            (int) array_values($ranges)[0],
            'Certificando que as inscrições com nota 18.00 são contadas na primeira faixa mesmo convivendo com inscrições ainda em avaliação'
        );

        $this->assertSame(
            2,
            array_sum(array_map('intval', $ranges)),
            'Certificando que somente as inscrições com nota são contadas no gráfico'
        );
    }

    public function testValidScoreIsCountedInTheCorrectRange(): void
    {
        $opportunity = $this->createTechnicalOpportunity();

        $registrations = $this->registrationDirector->createSentRegistrations($opportunity, 1);
        $this->setConsolidatedResult($registrations, '85.50');

        $ranges = $this->scoreRanges($opportunity);

        $this->assertSame(
            1,
            (int) array_values($ranges)[4],
            'Certificando que a nota 85.50 é contada na faixa de 81 a 100'
        );
    }

    public function testSourceGuardsForConsolidatedResultCast(): void
    {
        // A exportação em CSV repete a mesma consulta do gráfico. Se a guarda
        // existir só no módulo, o download continua respondendo 500.
        $sources = [
            'módulo (tela)' => $this->projectSrcPath('modules/Reports/Module.php'),
            'controller (CSV)' => $this->projectSrcPath('modules/Reports/Controller.php'),
        ];

        foreach ($sources as $origin => $path) {
            $code = file_get_contents($path);

            $this->assertStringNotContainsString(
                'cast(consolidated_result as DECIMAL)',
                $code,
                "Certificando que o {$origin} não converte consolidated_result sem antes verificar se o valor é numérico"
            );

            $this->assertStringContainsString(
                'CASE WHEN consolidated_result ~',
                $code,
                "Certificando que o {$origin} protege o cast com um CASE, que garante a ordem de avaliação — um predicado solto no WHERE pode ser reordenado pelo planner do PostgreSQL"
            );
        }
    }
}
