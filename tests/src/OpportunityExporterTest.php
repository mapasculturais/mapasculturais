<?php

namespace Test;

use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

use MapasCulturais\App;
use MapasCulturais\Entities\File;

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
            files: true,
            images: true,
            dates: true,
            vacancyLimits: true,
            workplan: false,
            statusLabels: true,
            appealPhases: true,
            monitoringPhases: true
        );

        $imported = null;

        $app = App::i();
        $original_log_handlers = $app->log->getHandlers();
        $app->log->setHandlers([]);

        try {
            $imported = $importer->import();
            $imported->save(true);
            $imported = $imported->refreshed();

            $this->assertImportedFilesMatchFixture($imported, $data);

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

                    $exported_attachments = $exported_phase['form']['attachments'] ?? [];
                    $this->assertCount(
                        count($exported_attachments),
                        $imported_phase->registrationFileConfigurations,
                        'Garantindo que os anexos do formulário importados correspondem ao fixture'
                    );
                }
                
                if(!$imported_phase->isFirstPhase) {
                    $this->assertEquals($exported_phase['name'], $imported_phase->name, 'Garantindo que o nome da fase ' . $i . ' é igual ao do fixture');
                }

                $this->assertEquals($exported_phase['statusLabels'], $imported_phase->statusLabels, 'Garantindo que os statusLabels da fase ' . $i . ' são iguais ao do fixture');
                
                if (isset($exported_phase['evaluationPhase']) && $exported_phase['evaluationPhase'] !== null) {
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
        } finally {
            $app->log->setHandlers($original_log_handlers);
            if ($imported) {
                $this->cleanupImportedOpportunityFiles($imported);
            }
        }
    }

    private function assertImportedFilesMatchFixture($imported, array $data): void
    {
        foreach (['files', 'images'] as $blockKey) {
            foreach (($data[$blockKey] ?? []) as $group => $fixtureFiles) {
                if (!is_array($fixtureFiles) || $fixtureFiles === []) {
                    continue;
                }

                $importedForGroup = $imported->getFiles($group);
                $expectedCount = count($fixtureFiles);
                $label = "{$blockKey} (grupo {$group})";

                if ($importedForGroup instanceof File) {
                    $this->assertGreaterThanOrEqual(1, $expectedCount, "Fixture de {$label} deveria ter ao menos um arquivo para grupo único");
                    $this->assertEquals(
                        $fixtureFiles[0]['name'],
                        $importedForGroup->name,
                        "Garantindo que o nome do arquivo importado em {$label} coincide com o do fixture"
                    );
                } else {
                    $this->assertIsArray(
                        $importedForGroup,
                        "Arquivos importados em {$label} deveriam ser uma lista"
                    );
                    $this->assertCount(
                        $expectedCount,
                        $importedForGroup,
                        "Garantindo que a quantidade de arquivos em {$label} é igual à do fixture"
                    );
                }
            }
        }
    }

    function testExportImportOpportunityWithoutDatesDoesNotIncludeEvaluationPhaseDates()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Informações')
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->refresh();

        $opportunity = $this->opportunityBuilder->getInstance();

        $exporter = new Exporter(
            $opportunity,
            infos: true,
            files: false,
            images: false,
            dates: false,
            vacancyLimits: false,
            workplan: false,
            statusLabels: false,
            appealPhases: false,
            monitoringPhases: false
        );

        $data = json_decode($exporter->export(), true);
        $exported_evaluation_phase = $data['phases'][0]['evaluationPhase'];

        $this->assertArrayNotHasKey('evaluationFrom', $exported_evaluation_phase, 'Garantindo que a exportação sem datas não inclui a data inicial da fase de avaliação');
        $this->assertArrayNotHasKey('evaluationTo', $exported_evaluation_phase, 'Garantindo que a exportação sem datas não inclui a data final da fase de avaliação');

        $importOwner = $this->userDirector->createUser()->profile;

        $importer = new Importer(
            $importOwner,
            $data,
            files: false,
            images: false,
            dates: false,
            vacancyLimits: false,
            workplan: false,
            statusLabels: false,
            appealPhases: false,
            monitoringPhases: false
        );

        $imported = $importer->import();
        $imported->save(true);
        $imported = $imported->refreshed();

        $this->assertNull($imported->evaluationMethodConfiguration->evaluationFrom, 'Garantindo que a importação sem datas não define a data inicial da fase de avaliação');
        $this->assertNull($imported->evaluationMethodConfiguration->evaluationTo, 'Garantindo que a importação sem datas não define a data final da fase de avaliação');
    }

    private function cleanupImportedOpportunityFiles($imported): void
    {
        $owners = [$imported, ...$imported->allPhases];
        $seen = [];

        foreach ($imported->allPhases as $phase) {
            foreach ($phase->registrationFileConfigurations as $rfc) {
                $owners[] = $rfc;
            }
        }

        foreach ($owners as $owner) {
            $oid = spl_object_id($owner);
            if (isset($seen[$oid])) {
                continue;
            }

            $seen[$oid] = true;
            $this->deleteAllOwnerFiles($owner);
        }
    }

    private function deleteAllOwnerFiles($owner): void
    {
        $app = App::i();
        $repo = $app->repo($owner->fileClassName);
        $files = $repo->findBy(['owner' => $owner]);

        foreach ($files as $file) {
            $file->delete(true);
        }
    }
}
