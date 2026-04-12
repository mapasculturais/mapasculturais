<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Enums\ProponentTypes;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityPhasesTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RequestFactory,
        UserDirector;

    /** Etapas explícitas do formulário (ignora step vazio criado pelo hook insert:after da oportunidade). */
    private const REGISTRATION_MODEL_TEST_STEP_NAMES = ['Etapa 1', 'Etapa 2', 'Etapa 3', 'Etapa 4'];


    /**
     * Garante que, após excluir a primeira fase de avaliação, a segunda fase de avaliação permaneça vinculada à primeira fase de coleta de dados.
     */
    function testFirstEvaluationPhaseDeletion() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                                ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->save()
                                    ->done()
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->refresh()
                                ->getInstance();
        

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();

        $phases = $opportunity->phases;

        $this->assertEquals($opportunity->id, $phases[1]->opportunity->id, "Garantindo que uma segunda fase de avaliação, após a exclusão da primeira fase de avaliação, esteja vinculada a primeira fase de coletada de dados");
    
    }

    /**
     * Garante que fases de avaliação encadeadas não sejam coleta de dados e que a exclusão de uma delas remova a oportunidade vinculada quando não for coleta de dados.
     */
    function testSecondEvaluationPhaseDeletion() {
        $app = $this->app;

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;
        
        /** @var Opportunity */
        $opportunity = $builder->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->getInstance();

        $eval_phase1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();
        


        $this->assertFalse((bool) $eval_phase2->opportunity->isDataCollection, 'Garantindo que a  Opportunity vinculada a uma fase de avaliação que segue outra fase de avaliação não é uma coleta de dados');

        $hidden_opportunity_id = $eval_phase2->opportunity->id;
        
        $eval_phase2->delete(flush: true);

        $hidden_opportunity = $app->repo('Opportunity')->find($hidden_opportunity_id);

        $this->assertNull($hidden_opportunity, 'Garantindo que a exclusão de uma fase de avaliação exclua a Opportunity vinculada quando essa não é uma coleta de dados');
    
    }

    function testFieldsVisibleForEvaluatorsPersistedPerPhase()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setCategories(['Categoria A'])
            ->setProponentTypes(['Pessoa Física'])
            ->setRanges([
                ['label' => 'Faixa 1', 'limit' => 10, 'value' => 1]
            ])
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('etapa principal')
                    ->createField('campo-a', 'text', title: 'Campo A')
                    ->createField('campo-b', 'text', title: 'Campo B')
                    ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('com-1', 1)
                ->save()
                ->addValuers(1, 'com-1')
                ->done()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new After)
                ->setCommitteeValuersPerRegistration('com-2', 1)
                ->save()
                ->addValuers(1, 'com-2')
                ->done()
            ->getInstance();

        $evaluation_phases = [];
        $phase = $opportunity->firstPhase;
        while ($phase) {
            if ($phase->evaluationMethodConfiguration) {
                $evaluation_phases[] = $phase;
            }
            $phase = $phase->nextPhase;
        }

        $this->assertCount(2, $evaluation_phases, 'Garantindo que a oportunidade possui 2 fases de avaliações');

        usort($evaluation_phases, function (Opportunity $a, Opportunity $b) {
            return $a->evaluationMethodConfiguration->evaluationFrom <=> $b->evaluationMethodConfiguration->evaluationFrom;
        });

        /** @var Opportunity $phase_1 */
        $phase_1 = $evaluation_phases[0];
        /** @var Opportunity $phase_2 */
        $phase_2 = $evaluation_phases[1];

        $field_a = $this->opportunityBuilder->getFieldName('campo-a');
        $field_b = $this->opportunityBuilder->getFieldName('campo-b');

        $phase_1->setAvaliableEvaluationFields([$field_a => 'true']);
        $phase_1->save(true);

        $phase_2->setAvaliableEvaluationFields([$field_b => 'true']);
        $phase_2->save(true);

        $registration_base = $this->registrationDirector->createSentRegistration(
            $opportunity->firstPhase,
            data: [
                $field_a => 'VALOR_A_BASE',
                $field_b => 'VALOR_B_BASE'
            ]
        );

        // Garante que as definições de metadados existem mesmo para fases futuras.
        $phase_1->registerRegistrationMetadata(true);
        $phase_2->registerRegistrationMetadata(true);

        $app = App::i();
        $phasesModule = $app->modules['OpportunityPhases'];

        // Cria os registros das fases de avaliações
        $registration_1 = $phasesModule->createPhaseRegistration($phase_1, $registration_base);
        $registration_1->$field_a = 'VALOR_A_P1';
        $registration_1->$field_b = 'VALOR_B_P1';
        $registration_1->save(true);
        $registration_1->send(false);
        $registration_1 = $registration_1->refreshed();

        $registration_2 = $phasesModule->createPhaseRegistration($phase_2, $registration_base);
        $registration_2->$field_a = 'VALOR_A_P2';
        $registration_2->$field_b = 'VALOR_B_P2';
        $registration_2->save(true);
        $registration_2->send(false);
        $registration_2 = $registration_2->refreshed();

        // Redistribui avaliadores para que viewUserEvaluation fique habilitado.
        $phase_1->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $phase_2->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $registration_1 = $registration_1->refreshed();
        $registration_2 = $registration_2->refreshed();

        $valuer_user_id_1 = array_key_first($registration_1->valuers);
        $valuer_user_id_2 = array_key_first($registration_2->valuers);

        $valuer_user_1 = $this->app->repo('User')->find((int) $valuer_user_id_1);
        $valuer_user_2 = $this->app->repo('User')->find((int) $valuer_user_id_2);

        $this->assertNotNull($valuer_user_1, 'Avaliador da fase 1 deve existir');
        $this->assertNotNull($valuer_user_2, 'Avaliador da fase 2 deve existir');

        // Phase 1: avaliador deve enxergar somente campo-a.
        $this->login($valuer_user_1);
        $json_1 = $registration_1->jsonSerialize();
        $this->assertArrayHasKey($field_a, $json_1, 'Campo habilitado deve aparecer no jsonSerialize do avaliador da fase 1');
        $this->assertFalse(isset($json_1[$field_b]), 'Campo desabilitado deve não aparecer no jsonSerialize do avaliador da fase 1');

        // Phase 2: avaliador deve enxergar somente campo-b.
        $this->login($valuer_user_2);
        $json_2 = $registration_2->jsonSerialize();
        $this->assertArrayHasKey($field_b, $json_2, 'Campo habilitado deve aparecer no jsonSerialize do avaliador da fase 2');
        $this->assertFalse(isset($json_2[$field_a]), 'Campo desabilitado deve não aparecer no jsonSerialize do avaliador da fase 2');
    }

    /**
     * Garante que salvar modelo e criar edital a partir do modelo duplicam etapas (registration_step)
     * e mantêm cada campo vinculado ao step da própria oportunidade (IDs diferentes, referências corretas).
     */
    function testOpportunityModelCreationAndReusePreservesRegistrationSteps(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $modelName = 'Modelo PHPUnit ' . uniqid('', true);
        $newEditalName = 'Edital novo ' . uniqid('', true);

        /** @var Opportunity $source */
        $source = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa 1', 0)
                ->createField('campo-a', 'text', 'Campo A')
                ->createStep('Etapa 2', 1)
                ->createField('campo-b', 'text', 'Campo B')
                ->createStep('Etapa 3', 2)
                ->createField('campo-c', 'text', 'Campo C')
                ->createStep('Etapa 4', 3)
                ->createField('campo-d1', 'text', 'Campo D1')
                ->createField('campo-d2', 'text', 'Campo D2')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $expectedStructure = $this->getRegistrationFormStructureByStep($source, self::REGISTRATION_MODEL_TEST_STEP_NAMES);
        $this->assertCount(4, $expectedStructure, 'Garantindo que o edital de origem tenha 4 etapas de inscrição');
        $this->assertSame(['Campo A'], $expectedStructure[0]['fields'], 'Garantindo que a etapa 1 contenha apenas o campo Campo A');
        $this->assertSame(['Campo B'], $expectedStructure[1]['fields'], 'Garantindo que a etapa 2 contenha apenas o campo Campo B');
        $this->assertSame(['Campo C'], $expectedStructure[2]['fields'], 'Garantindo que a etapa 3 contenha apenas o campo Campo C');
        $this->assertSame(['Campo D1', 'Campo D2'], $expectedStructure[3]['fields'], 'Garantindo que a etapa 4 contenha os campos Campo D1 e Campo D2');

        $this->assertRegistrationFieldsStepsBelongToOpportunity($source);

        $app = $this->app;
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generatemodel', [$source->id], ['id' => $source->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $source->id]);
        $controller->postData = [
            'name' => $modelName,
            'description' => 'Modelo gerado pelo teste automatizado',
            'entityId' => $source->id,
        ];

        try {
            $controller->ALL_generatemodel();
            $this->fail('Garantindo que ALL_generatemodel encerre com Halt após responder em JSON');
        } catch (Halt) {
        }

        /** @var Opportunity|null $model */
        $model = $app->repo('Opportunity')->findOneBy(['name' => $modelName]);
        $this->assertNotNull($model, 'Garantindo que o modelo exista após generatemodel');
        $this->assertNotEquals($source->id, $model->id, 'Garantindo que o modelo seja uma nova oportunidade distinta do edital de origem');

        $model = $model->refreshed();
        $this->assertRegistrationFieldsStepsBelongToOpportunity($model);

        $modelStructure = $this->getRegistrationFormStructureByStep($model, self::REGISTRATION_MODEL_TEST_STEP_NAMES);
        $this->assertEquals($expectedStructure, $modelStructure, 'Garantindo que a estrutura de etapas e campos do modelo espelhe o edital de origem');

        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generateopportunity', [$model->id], ['id' => $model->id]);
        $app->response = new Response();
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $model->id]);
        $controller->postData = [
            'name' => $newEditalName,
            'entityId' => $model->id,
            'objectType' => 'agent',
            'ownerEntity' => $admin->profile->id,
        ];

        try {
            $controller->ALL_generateopportunity();
            $this->fail('Garantindo que ALL_generateopportunity encerre com Halt após responder em JSON');
        } catch (Halt) {
        }

        $responseBody = (string) $app->response->getBody();
        $payload = json_decode($responseBody, true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('id', $payload, 'Garantindo que a resposta JSON inclua o id da nova oportunidade');

        /** @var Opportunity|null $fromModel */
        $fromModel = $app->repo('Opportunity')->find($payload['id']);
        $this->assertNotNull($fromModel, 'Garantindo que a nova oportunidade criada a partir do modelo exista');
        $this->assertNotEquals($source->id, $fromModel->id, 'Garantindo que o novo edital não seja o mesmo registro do edital de origem');
        $this->assertNotEquals($model->id, $fromModel->id, 'Garantindo que o novo edital não seja o mesmo registro do modelo');

        $fromModel = $fromModel->refreshed();
        $this->assertRegistrationFieldsStepsBelongToOpportunity($fromModel);

        $fromModelStructure = $this->getRegistrationFormStructureByStep($fromModel, self::REGISTRATION_MODEL_TEST_STEP_NAMES);
        $this->assertEquals($expectedStructure, $fromModelStructure, 'Garantindo que o novo edital repita a mesma organização por etapa do original');

        $sourceStepIds = $this->getRegistrationStepIdsOrdered($source, self::REGISTRATION_MODEL_TEST_STEP_NAMES);
        $modelStepIds = $this->getRegistrationStepIdsOrdered($model, self::REGISTRATION_MODEL_TEST_STEP_NAMES);
        $fromModelStepIds = $this->getRegistrationStepIdsOrdered($fromModel, self::REGISTRATION_MODEL_TEST_STEP_NAMES);

        $this->assertCount(4, $sourceStepIds, 'Garantindo que o edital de origem tenha 4 registration_step nas etapas nomeadas do teste');
        $this->assertCount(4, $modelStepIds, 'Garantindo que o modelo tenha 4 registration_step nas etapas nomeadas do teste');
        $this->assertCount(4, $fromModelStepIds, 'Garantindo que o novo edital tenha 4 registration_step nas etapas nomeadas do teste');

        $this->assertNotEquals($sourceStepIds, $modelStepIds, 'Garantindo que os ids de registration_step do modelo diferem dos do edital original');
        $this->assertNotEquals($modelStepIds, $fromModelStepIds, 'Garantindo que os ids de registration_step do novo edital diferem dos do modelo');
    }

    /**
     * Garante que categorias, tipos de proponente e faixas de inscrição são copiados ao gerar modelo e ao criar edital a partir do modelo.
     */
    function testOpportunityModelCopiesCategoriesProponentTypesAndRanges(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $expectedCategories = ['Categoria PHPUnit A', 'Categoria PHPUnit B'];
        $expectedProponentTypes = [
            ProponentTypes::PESSOA_FISICA->value,
            ProponentTypes::COLETIVO->value,
        ];
        $rangeLabel = 'Faixa PHPUnit';
        $rangeLimit = 11;
        $rangeValue = 3;

        $modelName = 'Modelo taxonomias ' . uniqid('', true);
        $newEditalName = 'Edital taxonomias ' . uniqid('', true);

        /** @var Opportunity $source */
        $source = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->setCategories($expectedCategories, number_of_random_categories: 0)
            ->setProponentTypes($expectedProponentTypes)
            ->addRange($rangeLabel, $rangeLimit, $rangeValue)
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $this->assertRegistrationTaxonomiesEqual(
            $expectedCategories,
            $expectedProponentTypes,
            $rangeLabel,
            $rangeLimit,
            $rangeValue,
            $source,
            'Garantindo que o edital de origem reflita categorias, tipos de proponente e faixa configurados no builder'
        );

        $app = $this->app;
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generatemodel', [$source->id], ['id' => $source->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $source->id]);
        $controller->postData = [
            'name' => $modelName,
            'description' => 'Modelo com categorias e faixas',
            'entityId' => $source->id,
        ];

        try {
            $controller->ALL_generatemodel();
            $this->fail('Garantindo que ALL_generatemodel encerre com Halt após responder em JSON');
        } catch (Halt) {
        }

        $model = $app->repo('Opportunity')->findOneBy(['name' => $modelName]);
        $this->assertNotNull($model, 'Garantindo que o modelo exista após generatemodel');
        $model = $model->refreshed();

        $this->assertRegistrationTaxonomiesEqual(
            $expectedCategories,
            $expectedProponentTypes,
            $rangeLabel,
            $rangeLimit,
            $rangeValue,
            $model,
            'Garantindo que o modelo copie categorias, tipos de proponente e faixas do edital de origem'
        );

        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generateopportunity', [$model->id], ['id' => $model->id]);
        $app->response = new Response();
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $model->id]);
        $controller->postData = [
            'name' => $newEditalName,
            'entityId' => $model->id,
            'objectType' => 'agent',
            'ownerEntity' => $admin->profile->id,
        ];

        try {
            $controller->ALL_generateopportunity();
            $this->fail('Garantindo que ALL_generateopportunity encerre com Halt após responder em JSON');
        } catch (Halt) {
        }

        $payload = json_decode((string) $app->response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $fromModel = $app->repo('Opportunity')->find($payload['id']);
        $this->assertNotNull($fromModel, 'Garantindo que a nova oportunidade criada a partir do modelo exista');
        $fromModel = $fromModel->refreshed();

        $this->assertRegistrationTaxonomiesEqual(
            $expectedCategories,
            $expectedProponentTypes,
            $rangeLabel,
            $rangeLimit,
            $rangeValue,
            $fromModel,
            'Garantindo que o novo edital criado a partir do modelo mantenha categorias, tipos de proponente e faixas'
        );
    }

    /**
     * @param list<string> $expectedCategories
     * @param list<string> $expectedProponentTypes
     */
    protected function assertRegistrationTaxonomiesEqual(
        array $expectedCategories,
        array $expectedProponentTypes,
        string $expectedRangeLabel,
        int $expectedRangeLimit,
        int $expectedRangeValue,
        Opportunity $opportunity,
        string $message
    ): void {
        $this->assertEquals($expectedCategories, (array) $opportunity->registrationCategories, $message . ' — categorias');

        $this->assertEquals($expectedProponentTypes, (array) $opportunity->registrationProponentTypes, $message . ' — tipos de proponente');

        $ranges = (array) $opportunity->registrationRanges;
        $this->assertNotEmpty($ranges, $message . ' — faixas não vazias');
        $found = false;
        foreach ($ranges as $range) {
            $range = (array) $range;
            $label = $range['label'] ?? null;
            $limit = isset($range['limit']) ? (int) $range['limit'] : null;
            $value = isset($range['value']) ? (int) $range['value'] : null;
            if ($label === $expectedRangeLabel && $limit === $expectedRangeLimit && $value === $expectedRangeValue) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, $message . ' — faixa com label, limite e valor esperados');
    }

    /**
     * @param list<string>|null $onlyStepNames se informado, restringe às etapas com esses nomes (ordem por displayOrder).
     * @return list<array{stepName: string, stepOrder: int, fields: list<string>}>
     */
    protected function getRegistrationFormStructureByStep(Opportunity $opportunity, ?array $onlyStepNames = null): array
    {
        $steps = $opportunity->registrationSteps->toArray();
        if ($onlyStepNames !== null) {
            $steps = array_values(array_filter($steps, fn ($s) => in_array($s->name, $onlyStepNames, true)));
        }
        usort($steps, fn ($a, $b) => $a->displayOrder <=> $b->displayOrder);

        $conn = $this->app->em->getConnection();
        $result = [];
        foreach ($steps as $step) {
            $titles = $conn->fetchFirstColumn(
                'SELECT title FROM registration_field_configuration WHERE opportunity_id = ? AND step_id = ? ORDER BY display_order ASC',
                [$opportunity->id, $step->id]
            );
            $result[] = [
                'stepName' => $step->name,
                'stepOrder' => (int) $step->displayOrder,
                'fields' => array_map('strval', $titles),
            ];
        }

        return $result;
    }

    /**
     * @param list<string>|null $onlyStepNames
     * @return list<int>
     */
    protected function getRegistrationStepIdsOrdered(Opportunity $opportunity, ?array $onlyStepNames = null): array
    {
        $steps = $opportunity->registrationSteps->toArray();
        if ($onlyStepNames !== null) {
            $steps = array_values(array_filter($steps, fn ($s) => in_array($s->name, $onlyStepNames, true)));
        }
        usort($steps, fn ($a, $b) => $a->displayOrder <=> $b->displayOrder);

        return array_map(fn ($s) => (int) $s->id, $steps);
    }

    protected function assertRegistrationFieldsStepsBelongToOpportunity(Opportunity $opportunity): void
    {
        $conn = $this->app->em->getConnection();
        $oppId = (int) $opportunity->id;

        $badFields = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM registration_field_configuration rfc
             INNER JOIN registration_step rs ON rs.id = rfc.step_id
             WHERE rfc.opportunity_id = ? AND rs.opportunity_id <> ?',
            [$oppId, $oppId]
        );
        $this->assertSame(0, $badFields, 'Garantindo que cada campo de inscrição aponte para registration_step da mesma oportunidade');

        $badFiles = (int) $conn->fetchOne(
            'SELECT COUNT(*) FROM registration_file_configuration rfile
             INNER JOIN registration_step rs ON rs.id = rfile.step_id
             WHERE rfile.opportunity_id = ? AND rs.opportunity_id <> ?',
            [$oppId, $oppId]
        );
        $this->assertSame(0, $badFiles, 'Garantindo que cada anexo de inscrição aponte para registration_step da mesma oportunidade');
    }
}
