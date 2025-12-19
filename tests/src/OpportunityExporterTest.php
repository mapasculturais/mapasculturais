<?php

namespace Test;

use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

use OpportunityExporter\Exporter;
use OpportunityExporter\Importer;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Enums\EvaluationMethods;
use Tests\Fixtures;

class OpportunityExporterTest extends TestCase
{
    use OpportunityBuilder,
        UserDirector;

    function testExportImportOpportunity()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity_vacancies = 12;
        $range_limit = 10;
        $range_value = 1;

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies($opportunity_vacancies)
            ->setCategories(['Cat A', 'Cat B'])
            ->setRanges([['label' => 'Range A', 'limit' => $range_limit, 'value' => $range_value]])
            ->setProponentTypes(['Pessoa Física', 'Coletivo'])
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Informações')
                ->createOwnerField(
                    identifier: 'data-nascimento',
                    entity_field: 'dataDeNascimento',
                    title: 'Data de Nascimento',
                    required: true
                )
                ->createField(
                    identifier: 'biografia',
                    field_type: 'textarea',
                    title: 'Biografia',
                    required: true
                )
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuers(2, 'committee 1')
                ->done()
            ->refresh();

        $opportunity = $this->opportunityBuilder->getInstance();

        // Exportação
        $exporter = new Exporter(
            $opportunity,
            infos: true,
            files: false,
            images: false,
            dates: true,
            vacancyLimits: true,
            workplan: false,
            statusLabels: false,
            appealPhases: false,
            monitoringPhases: false
        );

        $json = $exporter->export();
        $data = json_decode($json, true);

        // Importação
        $importOwner = $this->userDirector->createUser()->profile;

        $importer = new Importer(
            $importOwner,
            $data,
            files: false,
            images: false,
            dates: true,
            vacancyLimits: true,
            workplan: false,
            statusLabels: false,
            appealPhases: false,
            monitoringPhases: false
        );

        $imported = $importer->import();
        $imported->save(true);
        $imported = $imported->refreshed();

        // Verificações básicas
        $this->assertEquals($data['infos']['properties']['name'], $imported->name, 'Garantindo que o nome importado é igual ao exportado');
        $this->assertEquals($data['infos']['properties']['shortDescription'], $imported->shortDescription, 'Garantindo que a descrição curta importada é igual à exportada');

        // categorias / faixas / tipos de proponentes
        $this->assertEquals($data['categories'], $imported->registrationCategories, 'Garantindo que as categorias importadas são iguais às exportadas');
        $this->assertEquals($data['ranges'], $imported->registrationRanges, 'Garantindo que as faixas importadas são iguais às exportadas');
        $this->assertEquals($data['proponentTypes']['registrationProponentTypes'], $imported->registrationProponentTypes, 'Garantindo que os tipos de proponentes importados são iguais às exportadas');

        // limites de vagas
        if (isset($data['vacancyLimits'])) {
            $this->assertEquals($data['vacancyLimits']['registrationLimit'], $imported->registrationLimit, 'Garantindo que o limite de inscrições importado é igual ao exportado');
            $this->assertEquals($data['vacancyLimits']['registrationLimitPerOwner'], $imported->registrationLimitPerOwner, 'Garantindo que o limite de inscrições por proprietário importado é igual ao exportado');
            $this->assertEquals($data['vacancyLimits']['vacancies'], $imported->vacancies, 'Garantindo que o número de vagas importado é igual ao exportado');
        }

        // verificação de steps, fase e campos
        $exported_first_phase = $data['phases'][0];
        if (isset($exported_first_phase['form'])) {
            $this->assertEquals(count($exported_first_phase['form']['steps']), count($imported->registrationSteps->toArray()), 'Garantindo que o número de steps importados é igual ao exportado');
            $this->assertEquals(count($exported_first_phase['form']['fields']), count($imported->firstPhase->registrationFieldConfigurations), 'Garantindo que o número de fields importados é igual ao exportado');
            $this->assertEquals($exported_first_phase['evaluationPhase']['type'], $imported->evaluationMethodConfiguration->type->id, 'Garantindo que a fase de avaliação é igual ao exportado');
        }
    }

    function testImportOpportunity()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $data = Fixtures::getJSON('exported-opportunity.json');

        $importOwner = $this->userDirector->createUser()->profile;

        $importer = new Importer(
            $importOwner,
            $data,
            files: false,
            images: false,
            dates: true,
            vacancyLimits: true,
            workplan: false,
            statusLabels: true,
            appealPhases: true,
            monitoringPhases: true
        );
        
        $imported = $importer->import();
        $imported->save(true);
        $imported = $imported->refreshed();

        // verificação do número total de fases
        $imported_phases = $imported->allPhases;
        $this->assertCount(count($data['phases']), $imported_phases, 'Garantindo que o número de fases importadas é igual ao do fixture');

        $total_phases = count($data['phases']);
        for($i = 0; $i < $total_phases; $i++) {
            $exported_phase = $data['phases'][$i];
            $imported_phase = $imported_phases[$i];

            if($imported_phase->isFirstPhase) {
                $this->assertEquals($data['infos']['properties']['name'], $imported_phase->name, 'Garantindo que o nome da fase ' . $i . ' é igual ao do fixture');
                $this->assertEquals($data['infos']['properties']['shortDescription'], $imported_phase->shortDescription, 'Garantindo que a descrição curta importada é igual ao do fixture');

                if (isset($data['vacancyLimits'])) {
                    $this->assertEquals($data['vacancyLimits']['registrationLimit'], $imported_phase->registrationLimit, 'Garantindo que o limite de inscrições importado é igual ao do fixture');
                    $this->assertEquals($data['vacancyLimits']['registrationLimitPerOwner'], $imported_phase->registrationLimitPerOwner, 'Garantindo que o limite de inscrições por proprietário importado é igual ao do fixture');
                    $this->assertEquals($data['vacancyLimits']['vacancies'], $imported_phase->vacancies, 'Garantindo que o número de vagas importado é igual ao do fixture');
                    
                    if (isset($data['vacancyLimits']['totalResource'])) {
                        $this->assertEquals($data['vacancyLimits']['totalResource'], $imported_phase->totalResource, 'Garantindo que o recurso total importado é igual ao do fixture');
                    }
                }
                
                $this->assertEquals(count($exported_phase['form']['steps']), count($imported_phase->registrationSteps->toArray()), 'Garantindo que o número de steps da fase é igual ao do fixture');
                $this->assertEquals(count($exported_phase['form']['fields']), count($imported_phase->registrationFieldConfigurations), 'Garantindo que o número de fields da fase é igual ao do fixture');
            }
            
            if(!$imported_phase->isFirstPhase) {
                $this->assertEquals($exported_phase['name'], $imported_phase->name, 'Garantindo que o nome da fase ' . $i . ' é igual ao do fixture');
            }

            $this->assertEquals($exported_phase['statusLabels'], $imported_phase->statusLabels, 'Garantindo que os statusLabels da fase ' . $i . ' são iguais ao do fixture');
            
            if(!$imported_phase->isLastPhase) {
                $this->assertEquals($exported_phase['evaluationPhase']['type'], $imported_phase->evaluationMethodConfiguration->type->id, 'Garantindo que o tipo de avaliação da fase ' . $i . ' é igual ao do fixture');
            }

            if (isset($exported_phase['appealPhase']) && $exported_phase['appealPhase'] !== null) {
                $this->assertNotNull($imported_phase->appealPhase, 'Garantindo que a fase ' . $i . ' tem appeal phase');
                
                // Verificação de statusLabels da appeal phase
                if (isset($exported_phase['appealPhase']['statusLabels'])) {
                    $this->assertEquals($exported_phase['appealPhase']['statusLabels'], $imported_phase->appealPhase->statusLabels, 'Garantindo que os statusLabels da fase de recurso da fase ' . $i . ' são iguais aos do fixture');
                }
                
                // Verificação do tipo de avaliação da appeal phase
                if (isset($exported_phase['appealPhase']['evaluationPhase']['type'])) {
                    $this->assertEquals($exported_phase['appealPhase']['evaluationPhase']['type'], $imported_phase->appealPhase->evaluationMethodConfiguration->type->id, 'Garantindo que o tipo de avaliação da fase de recurso da fase ' . $i . ' é igual ao do fixture');
                }
            }
        }
    }
}
