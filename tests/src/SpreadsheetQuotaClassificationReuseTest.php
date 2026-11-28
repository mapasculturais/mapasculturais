<?php

namespace Tests;

use EvaluationMethodTechnical\Module;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use ReflectionProperty;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Exportação da planilha de inscrições ordenada por classificação final (@quota).
 *
 * A planilha é gerada em lotes no mesmo processo. A classificação por cotas é
 * calculada no primeiro lote e reaproveitada nos demais (antes era recalculada
 * a cada lote, o que tornava a exportação inviável em editais grandes), sem
 * mudar o conteúdo nem a ordem da planilha.
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml run --rm mapas phpunit /var/www/tests --filter SpreadsheetQuotaClassificationReuseTest
 */
class SpreadsheetQuotaClassificationReuseTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private const JOB_SLUG = 'registrations-spreadsheets';

    private const BATCH_SIZE = 4;

    /** nota e autodeclaração de cada inscrição, com empates de nota */
    private const REGISTRATIONS = [
        [90.0, 'Não'], [85.0, 'Não'], [85.0, 'Sim'], [80.0, 'Não'], [72.5, 'Sim'],
        [70.0, 'Não'], [70.0, 'Não'], [65.0, 'Sim'], [60.0, 'Não'], [55.0, 'Sim'],
        [40.0, 'Não'], [30.0, 'Sim'], [10.0, 'Não'],
    ];

    private function createTechnicalOpportunityWithQuota(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(6)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Informações')
                ->createField('cota', 'select', 'Autodeclaração', required: false, options: ['Sim', 'Não'])
                ->save()
                ->done();

        $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(0)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Cotistas', 2)
                            ->addRuleField('cota', ['Sim'])
                    ->done()
                ->done()
                ->save()
                ->done()
            ->save();

        $opportunity = $this->opportunityBuilder->getInstance()->refreshed();
        $field_name = $this->opportunityBuilder->getFieldName('cota', $opportunity);

        foreach (self::REGISTRATIONS as [$score, $declaration]) {
            $registration = $this->registrationDirector->createSentRegistration($opportunity, data: [$field_name => $declaration]);
            App::i()->conn->executeQuery(
                'UPDATE registration SET score = :score, eligible = true WHERE id = :id',
                ['id' => $registration->id, 'score' => $score]
            );
        }

        App::i()->em->clear();

        return $opportunity->refreshed();
    }

    private function createJob(Opportunity $opportunity)
    {
        $app = App::i();

        return $app->enqueueOrReplaceJob(self::JOB_SLUG, [
            'owner' => $opportunity,
            'authenticatedUser' => $app->user,
            'extension' => 'csv',
            'entityClassName' => Registration::class,
            'query' => [
                '@select' => 'number,usingQuota',
                '@order' => '@quota',
                '@opportunity' => $opportunity->id,
            ],
        ]);
    }

    /**
     * Classificação calculada numa única consulta, sem paginação.
     *
     * @return array<string, mixed> número da inscrição => usingQuota, na ordem da classificação
     */
    private function getClassificationInSingleQuery(Opportunity $opportunity): array
    {
        $app = App::i();
        $result = $app->controller('opportunity')->apiFindRegistrations($opportunity, [
            '@select' => 'number,usingQuota',
            '@order' => '@quota',
            '@opportunity' => $opportunity->id,
        ], true);

        $classification = [];
        foreach ($result->registrations as $registration) {
            $classification[$registration['number']] = $registration['usingQuota'] ?? null;
        }

        return $classification;
    }

    function testExportReusesQuotaClassificationAcrossBatches(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithQuota();
        $expected = $this->getClassificationInSingleQuery($opportunity);
        Module::$quotaData = null;

        $app = App::i();
        $job_type = $app->getRegisteredJobType(self::JOB_SLUG);

        $limit = new ReflectionProperty($job_type, 'limit');
        $limit->setAccessible(true);
        $limit->setValue($job_type, self::BATCH_SIZE);

        $page = new ReflectionProperty($job_type, 'page');
        $page->setAccessible(true);
        $page->setValue($job_type, 1);

        $job = $this->createJob($opportunity);

        $exported = [];
        $batches = 0;
        $classification = null;

        while ($batch = $job_type->getBatch($job)) {
            $batches++;

            foreach ($batch as $row) {
                $exported[] = $row;
            }

            $this->assertIsArray(Module::$quotaOrderCache, "Certificando que a classificação fica disponível entre os lotes (lote {$batches})");
            $this->assertCount(1, Module::$quotaOrderCache['items'], "Certificando que há uma única classificação calculada para a exportação (lote {$batches})");

            $current = reset(Module::$quotaOrderCache['items']);
            if ($batches == 1) {
                $classification = $current;
            }

            $this->assertSame($classification, $current, "Certificando que o lote {$batches} reaproveita a classificação calculada no primeiro lote");
            $this->assertSame($classification->quota, Module::$quotaData->quota, "Certificando que o lote {$batches} usa os campos de cota da classificação reaproveitada");
        }

        $count = count(self::REGISTRATIONS);
        $this->assertSame((int) ceil($count / self::BATCH_SIZE), $batches, 'Certificando que a exportação foi feita em vários lotes');
        $this->assertNull(Module::$quotaOrderCache, 'Certificando que a classificação reaproveitada é descartada ao fim da exportação');

        $exported_numbers = array_column($exported, 'number');
        $this->assertCount($count, $exported_numbers, 'Certificando que todas as inscrições foram exportadas');
        $this->assertSame($exported_numbers, array_values(array_unique($exported_numbers)), 'Certificando que nenhuma inscrição foi exportada em duplicidade');
        $this->assertSame(array_keys($expected), $exported_numbers, 'Certificando que a planilha segue a ordem da classificação final');

        $exported_quotas = array_combine($exported_numbers, array_column($exported, 'usingQuota'));
        $this->assertSame(
            array_map(fn($using_quota) => $using_quota ?: null, $expected),
            array_map(fn($using_quota) => $using_quota ?: null, $exported_quotas),
            'Certificando que as inscrições classificadas como cotistas na planilha são as mesmas da classificação final'
        );
        $this->assertNotEmpty(array_filter($expected), 'Certificando que o cenário classificou alguma inscrição como cotista');
    }

    function testRegistrationListDoesNotReuseQuotaClassification(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithQuota();

        $app = App::i();
        foreach ([1, 2] as $page) {
            $app->controller('opportunity')->apiFindRegistrations($opportunity, [
                '@select' => 'number,usingQuota',
                '@order' => '@quota',
                '@opportunity' => $opportunity->id,
                '@limit' => self::BATCH_SIZE,
                '@page' => $page,
            ], true);

            $this->assertNull(Module::$quotaOrderCache, "Certificando que fora da exportação a classificação não é reaproveitada (página {$page})");
        }
    }
}
