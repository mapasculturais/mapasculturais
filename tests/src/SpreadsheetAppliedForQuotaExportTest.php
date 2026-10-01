<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use ReflectionMethod;
use ReflectionProperty;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão: coluna "A inscrição está concorrendo por cotas?" (`appliedForQuota`)
 * deve sair como Sim/Não na planilha de inscritos (não 1/vazio).
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/SpreadsheetAppliedForQuotaExportTest.php
 */
class SpreadsheetAppliedForQuotaExportTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private const JOB_SLUG = 'registrations-spreadsheets';

    private function createOpportunityWithQuotaQuestion(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $opportunity->enableQuotasQuestion = true;
        $opportunity->save(true);

        return $opportunity->refreshed();
    }

    private function createJob(Opportunity $opportunity, string $select)
    {
        $app = App::i();

        return $app->enqueueOrReplaceJob(self::JOB_SLUG, [
            'owner' => $opportunity,
            'authenticatedUser' => $app->user,
            'extension' => 'csv',
            'entityClassName' => Registration::class,
            'query' => [
                '@select' => $select,
                '@opportunity' => $opportunity->id,
            ],
        ]);
    }

    private function invoke(string $method_name, ...$arguments)
    {
        $job_type = App::i()->getRegisteredJobType(self::JOB_SLUG);

        $method = new ReflectionMethod($job_type, $method_name);
        $method->setAccessible(true);

        return $method->invoke($job_type, ...$arguments);
    }

    private function getBatchRows(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);

        $page = new ReflectionProperty(App::i()->getRegisteredJobType(self::JOB_SLUG), 'page');
        $page->setAccessible(true);
        $page->setValue(App::i()->getRegisteredJobType(self::JOB_SLUG), 1);

        return $this->invoke('_getBatch', $job);
    }

    public function testAppliedForQuotaIsExportedAsSimOrNao(): void
    {
        $opportunity = $this->createOpportunityWithQuotaQuestion();

        $yes = $this->registrationDirector->createSentRegistration($opportunity, [
            'appliedForQuota' => true,
        ]);
        $no = $this->registrationDirector->createSentRegistration($opportunity, [
            'appliedForQuota' => false,
        ]);

        $header = $this->invoke('_getHeader', $this->createJob(
            $opportunity,
            'number,appliedForQuota'
        ));

        $this->assertSame(
            'A inscrição está concorrendo por cotas?',
            $header['appliedForQuota'] ?? null,
            'Cabeçalho da coluna appliedForQuota deve usar o rótulo amigável'
        );

        $rows = $this->getBatchRows($opportunity, 'number,appliedForQuota');
        $this->assertNotEmpty($rows, 'A exportação precisa retornar as inscrições');

        $by_number = [];
        foreach ($rows as $row) {
            $by_number[$row['number']] = $row['appliedForQuota'] ?? null;
        }

        $this->assertSame(
            'Sim',
            $by_number[$yes->number] ?? null,
            'Inscrição com appliedForQuota=true deve exportar Sim'
        );
        $this->assertSame(
            'Não',
            $by_number[$no->number] ?? null,
            'Inscrição com appliedForQuota=false deve exportar Não'
        );
    }
}
