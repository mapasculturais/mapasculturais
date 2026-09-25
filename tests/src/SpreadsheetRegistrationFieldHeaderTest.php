<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use ReflectionMethod;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

/**
 * Regressão: headers de campos do formulário na planilha de inscritos devem
 * usar o título do campo (label), não o fieldName (`field_119`).
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/SpreadsheetRegistrationFieldHeaderTest.php
 */
class SpreadsheetRegistrationFieldHeaderTest extends TestCase
{
    use OpportunityBuilder;
    use UserDirector;

    private const JOB_SLUG = 'registrations-spreadsheets';
    private const FIELD_IDENTIFIER = 'campo_biografia';
    private const FIELD_TITLE = 'Biografia do proponente';

    private function createOpportunityWithField(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        return $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa 1')
                ->createField(self::FIELD_IDENTIFIER, 'textarea', self::FIELD_TITLE)
                ->done()
            ->save()
            ->getInstance();
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

    public function testRegistrationFieldHeaderUsesFieldTitleNotFieldId(): void
    {
        $opportunity = $this->createOpportunityWithField();
        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);

        $this->assertStringStartsWith('field_', $field_name, 'Pré-condição: fieldName deve ser field_*');

        // Campo no @select (como na exportação pela UI) — antes reservava a chave com field_*
        // e o 2º loop não sobrescrevia o título por causa do !isset.
        $header = $this->invoke('_getHeader', $this->createJob(
            $opportunity,
            "number,{$field_name},status"
        ));

        $this->assertArrayHasKey($field_name, $header, 'Coluna do campo deve existir no header');
        $this->assertSame(
            self::FIELD_TITLE,
            $header[$field_name],
            'Cabeçalho deve usar o título do campo, não o fieldName'
        );
        $this->assertNotSame(
            $field_name,
            $header[$field_name],
            'Cabeçalho não pode ser o fieldId bruto'
        );
    }
}
