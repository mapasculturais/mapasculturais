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
 * Regressão: campo do tipo `addresses` (ex.: "Endereço do espaço") saía com a
 * coluna vazia na planilha de inscritos, mesmo com o endereço salvo na inscrição.
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/SpreadsheetAddressesFieldExportTest.php
 */
class SpreadsheetAddressesFieldExportTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private const JOB_SLUG = 'registrations-spreadsheets';
    private const FIELD_IDENTIFIER = 'endereco_espaco';
    private const FIELD_TITLE = 'Endereço do espaço';

    private function createOpportunityWithAddressesField(): Opportunity
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
                ->createField(self::FIELD_IDENTIFIER, 'addresses', self::FIELD_TITLE)
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

    private function getBatchRows(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);

        $page = new ReflectionProperty(App::i()->getRegisteredJobType(self::JOB_SLUG), 'page');
        $page->setAccessible(true);
        $page->setValue(App::i()->getRegisteredJobType(self::JOB_SLUG), 1);

        return $this->invoke('_getBatch', $job);
    }

    public function testAddressesFieldIsExportedWithHeaderAndValue(): void
    {
        $opportunity = $this->createOpportunityWithAddressesField();
        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);
        $select = "number,{$field_name}";

        $registration = $this->registrationDirector->createSentRegistration($opportunity, [
            $field_name => [(object) [
                'nome' => 'Espaço Cultural Kasa Raiz',
                'logradouro' => 'Rua São José dos Campos',
                'numero' => '10',
                'bairro' => 'Centro',
                'cidade' => 'Vila Velha',
                'estado' => 'ES',
                'cep' => '29135-413',
                'complemento' => 'Sala 2',
            ]],
        ]);

        // A planilha monta cada linha a partir das chaves do cabeçalho:
        // se o campo sumir daqui, a coluna some da planilha.
        $header = $this->invoke('_getHeader', $this->createJob($opportunity, $select));
        $this->assertSame(
            self::FIELD_TITLE,
            $header[$field_name] ?? null,
            'Cabeçalho deve ter a coluna do campo addresses com o título do campo'
        );

        $rows = $this->getBatchRows($opportunity, $select);
        $by_number = array_column($rows, $field_name, 'number');

        $this->assertSame(
            'Espaço Cultural Kasa Raiz: Rua São José dos Campos, nº 10 - Centro, Vila Velha/ES, 29135-413 (Sala 2)',
            $by_number[$registration->number] ?? null,
            'Célula do campo addresses deve trazer o endereço formatado, não vazia'
        );
    }
}
