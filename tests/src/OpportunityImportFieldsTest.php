<?php

namespace Test;

use DateTime;
use MapasCulturais\Exceptions\Halt;
use Slim\Psr7\Response;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;
use Tests\Traits\RequestFactory;
use Tests\Enums\EvaluationMethods;
use Tests\Enums\ProponentTypes;

class OpportunityImportFieldsTest extends TestCase
{
    use OpportunityBuilder,
        UserDirector,
        RequestFactory;

    /**
     * Cria uma oportunidade com campos, anexos e metadados configurados na primeira fase
     * e retorna o source de importação no formato do TXT exportado.
     */
    protected function createSourceOpportunity(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->setCategories(['Cat A', 'Cat B'])
            ->setRanges([['label' => 'Range A', 'limit' => 10, 'value' => 1]])
            ->setProponentTypes(['Pessoa Física', 'Coletivo'])
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Informações')
                ->createOwnerField(
                    identifier: 'data-nascimento',
                    entity_field: 'dataDeNascimento',
                    title: 'Data de Nascimento',
                    required: true,
                    categories: ['Cat A'],
                    ranges: ['Range A'],
                    proponent_types: ['Pessoa Física']
                )
                ->createField(
                    identifier: 'biografia',
                    field_type: 'textarea',
                    title: 'Biografia',
                    required: true,
                    categories: ['Cat B'],
                    ranges: [],
                    proponent_types: ['Coletivo']
                )
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new Open)
                ->save()
                ->done()
            ->refresh();

        $source_opportunity = $this->opportunityBuilder->getInstance();

        $fields = $source_opportunity->registrationFieldConfigurations;
        $files = $source_opportunity->registrationFileConfigurations;

        return [
            'opportunity' => $source_opportunity,
            'admin' => $admin,
            'source' => [
                'fields' => $this->serializeFields($fields),
                'files' => $this->serializeFiles($files),
                'meta' => $this->serializeMetadata($source_opportunity),
            ],
        ];
    }

    protected function serializeFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[] = [
                'fieldName' => $field->fieldName,
                'title' => $field->title,
                'description' => $field->description,
                'maxSize' => $field->maxSize,
                'fieldType' => $field->fieldType,
                'required' => $field->required,
                'categories' => $field->categories ?: [],
                'config' => $field->config,
                'fieldOptions' => $field->fieldOptions,
                'displayOrder' => $field->displayOrder,
                'conditional' => $field->conditional,
                'conditionalField' => $field->conditionalField,
                'conditionalValue' => $field->conditionalValue,
                'proponentTypes' => $field->proponentTypes ?: [],
                'registrationRanges' => $field->registrationRanges ?: [],
                'step' => [
                    'name' => $field->step->name,
                    'displayOrder' => $field->step->displayOrder,
                ],
            ];
        }
        return $result;
    }

    protected function serializeFiles(array $files): array
    {
        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'fieldName' => $file->fileGroupName,
                'title' => $file->title,
                'description' => $file->description,
                'required' => $file->required,
                'categories' => $file->categories ?: [],
                'displayOrder' => $file->displayOrder,
                'conditional' => $file->conditional,
                'conditionalField' => $file->conditionalField,
                'conditionalValue' => $file->conditionalValue,
                'proponentTypes' => $file->proponentTypes ?: [],
                'registrationRanges' => $file->registrationRanges ?: [],
                'step' => [
                    'name' => $file->step->name,
                    'displayOrder' => $file->step->displayOrder,
                ],
                'template' => null,
            ];
        }
        return $result;
    }

    protected function serializeMetadata($opportunity): array
    {
        return [
            'registrationCategories' => $opportunity->registrationCategories,
            'registrationRanges' => $opportunity->registrationRanges,
            'registrationProponentTypes' => $opportunity->registrationProponentTypes,
            'useAgentRelationColetivo' => 'dontUse',
            'useAgentRelationInstituicao' => 'dontUse',
            'useSpaceRelationIntituicao' => 'dontUse',
            'registrationCategDescription' => 'Descrição das categorias',
            'registrationCategTitle' => 'Categorias',
            'introInscricoes' => 'Introdução',
            'registrationSeals' => (object) [],
            'registrationLimit' => 100,
            'registrationLimitPerOwner' => 2,
            'isContinuousFlow' => false,
            'hasEndDate' => false,
            'continuousFlow' => null,
            'publishTimestamp' => null,
            'registrationTo' => $opportunity->registrationTo ? $opportunity->registrationTo->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Cria uma oportunidade de destino vazia, com primeira fase e (opcionalmente) fase filha.
     */
    protected function createTargetOpportunity(bool $with_child_phase = false, bool $appeal_phase = false): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save();

        $builder
            ->setCategories(['Cat X'])
            ->setRanges([['label' => 'Range X', 'limit' => 5, 'value' => 2]])
            ->setProponentTypes(['MEI'])
            ->save();

        $opportunity = $builder->getInstance();

        if ($with_child_phase) {
            $builder
                ->addDataCollectionPhase()
                    ->setRegistrationPeriod(new Open)
                    ->done()
                ->save()
                ->refresh();

            $opportunity = $builder->getInstance()->refreshed();
            $child_phase = array_filter(
                $opportunity->allPhases,
                fn($phase) => !$phase->isFirstPhase
            );
            $child_phase = reset($child_phase);
        } else {
            $child_phase = null;
        }

        if ($appeal_phase && $child_phase) {
            $this->createAppealPhase($child_phase);
            $opportunity = $builder->getInstance()->refreshed();
            $appeal = $child_phase->refreshed()->appealPhase;
            return ['opportunity' => $opportunity, 'child' => $child_phase, 'appeal' => $appeal, 'admin' => $admin];
        }

        return ['opportunity' => $opportunity, 'child' => $child_phase, 'admin' => $admin];
    }

    protected function createAppealPhase($opportunity): void
    {
        $app = $this->app;
        $opportunityId = $opportunity->id;

        $app->request = $this->requestFactory->mapasPOST(
            'opportunity',
            'createAppealPhase',
            [$opportunityId],
            ['id' => $opportunityId]
        );
        $app->response = new Response();

        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $opportunityId]);
        try {
            $controller->callAction('POST', 'createAppealPhase', []);
        } catch (Halt $e) {
        }
    }

    protected function importFields($opportunity, array $source): void
    {
        $this->app->disableAccessControl();
        try {
            // Simula o fluxo real do TXT: encode para JSON e decode de volta
            $json = json_encode([
                'fields' => $source['fields'],
                'files' => $source['files'],
                'meta' => $source['meta'],
            ]);
            $importSource = json_decode($json);

            $opportunity->importFields($importSource);
        } finally {
            $this->app->enableAccessControl();
        }
    }

    /**
     * Cenário 1: importar formulário completo na primeira fase.
     */
    function testImportFieldsInFirstPhaseImportsEverything()
    {
        $source_data = $this->createSourceOpportunity();
        $target = $this->createTargetOpportunity();

        $this->login($target['admin']);
        $opportunity = $target['opportunity'];
        $this->importFields($opportunity, $source_data['source']);
        $opportunity = $opportunity->refreshed();

        $this->assertTrue($opportunity->isFirstPhase, 'A oportunidade de destino é a primeira fase');
        $this->assertEquals(['Cat A', 'Cat B'], $opportunity->registrationCategories, 'Categorias importadas na primeira fase');
        $this->assertEquals(['Pessoa Física', 'Coletivo'], $opportunity->registrationProponentTypes, 'Tipos de proponente importados na primeira fase');
        $this->assertEquals('Categorias', $opportunity->registrationCategTitle, 'Título das categorias importado na primeira fase');
        $this->assertEquals(100, $opportunity->registrationLimit, 'Limite de inscrições importado na primeira fase');

        $fields = $opportunity->registrationFieldConfigurations;
        $this->assertCount(2, $fields, 'Campos importados na primeira fase');

        $biografia = array_values(array_filter($fields, fn($f) => $f->title === 'Biografia'))[0] ?? null;
        $this->assertNotNull($biografia, 'Campo Biografia importado');
        $this->assertEquals(['Cat B'], $biografia->categories, 'Categorias do campo importadas na primeira fase');
        $this->assertEquals(['Coletivo'], $biografia->proponentTypes, 'Tipos de proponente do campo importados na primeira fase');
    }

    /**
     * Cenário 2: importar formulário em fase filha.
     * Os metadados exclusivos da primeira fase não devem ser sobrescritos,
     * mas os campos/anexos devem manter suas categorias/faixas/tipos.
     */
    function testImportFieldsInChildPhaseKeepsFirstPhaseOnlyMetadata()
    {
        $source_data = $this->createSourceOpportunity();
        $target = $this->createTargetOpportunity(with_child_phase: true);

        $child_phase = $target['child'];
        $this->assertNotNull($child_phase, 'Fase filha criada');

        $original_child_categories = $child_phase->registrationCategories;
        $original_child_ranges = $child_phase->registrationRanges;
        $original_child_proponents = $child_phase->registrationProponentTypes;

        $this->login($target['admin']);
        $this->importFields($child_phase, $source_data['source']);

        $child_phase = $child_phase->refreshed();

        $this->assertEquals($original_child_categories, $child_phase->registrationCategories, 'Categorias globais da fase filha não foram alteradas');
        $this->assertEquals($original_child_ranges, $child_phase->registrationRanges, 'Faixas globais da fase filha não foram alteradas');
        $this->assertEquals($original_child_proponents, $child_phase->registrationProponentTypes, 'Tipos de proponente globais da fase filha não foram alterados');
        $this->assertNotEquals('Categorias', $child_phase->registrationCategTitle, 'Título das categorias não importado na fase filha');

        $fields = $child_phase->registrationFieldConfigurations;
        $this->assertCount(2, $fields, 'Campos importados na fase filha');

        $biografia = array_values(array_filter($fields, fn($f) => $f->title === 'Biografia'))[0] ?? null;
        $this->assertNotNull($biografia, 'Campo Biografia importado na fase filha');
        $this->assertEquals(['Cat B'], $biografia->categories, 'Categorias do campo preservadas na fase filha');
        $this->assertEquals(['Coletivo'], $biografia->proponentTypes, 'Tipos de proponente do campo preservados na fase filha');
    }

    /**
     * Cenário 3: importar formulário em fase de recurso.
     * Comportamento deve ser o mesmo da fase filha: metadados globais da primeira fase não são sobrescritos,
     * mas os campos/anexos mantêm suas configurações por categoria/faixa/tipo.
     */
    function testImportFieldsInAppealPhaseKeepsFirstPhaseOnlyMetadata()
    {
        $source_data = $this->createSourceOpportunity();
        $target = $this->createTargetOpportunity(with_child_phase: true, appeal_phase: true);

        $appeal_phase = $target['appeal'];
        $this->assertNotNull($appeal_phase, 'Fase de recurso criada');

        $original_appeal_categories = $appeal_phase->registrationCategories;
        $original_appeal_ranges = $appeal_phase->registrationRanges;
        $original_appeal_proponents = $appeal_phase->registrationProponentTypes;

        $this->login($target['admin']);
        $this->importFields($appeal_phase, $source_data['source']);

        $appeal_phase = $appeal_phase->refreshed();

        $this->assertEquals($original_appeal_categories, $appeal_phase->registrationCategories, 'Categorias globais da fase de recurso não foram alteradas');
        $this->assertEquals($original_appeal_ranges, $appeal_phase->registrationRanges, 'Faixas globais da fase de recurso não foram alteradas');
        $this->assertEquals($original_appeal_proponents, $appeal_phase->registrationProponentTypes, 'Tipos de proponente globais da fase de recurso não foram alterados');

        $fields = $appeal_phase->registrationFieldConfigurations;
        $this->assertCount(2, $fields, 'Campos importados na fase de recurso');

        $biografia = array_values(array_filter($fields, fn($f) => $f->title === 'Biografia'))[0] ?? null;
        $this->assertNotNull($biografia, 'Campo Biografia importado na fase de recurso');
        $this->assertEquals(['Cat B'], $biografia->categories, 'Categorias do campo preservadas na fase de recurso');
        $this->assertEquals(['Coletivo'], $biografia->proponentTypes, 'Tipos de proponente do campo preservados na fase de recurso');
    }
}
