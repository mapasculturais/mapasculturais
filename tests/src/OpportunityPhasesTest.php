<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Enums\ProponentTypes;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityPhasesTest extends TestCase
{
    use AgentDirector,
        OpportunityBuilder,
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
     * Garante que, ao excluir fases intermediárias, as avaliações restantes permaneçam vinculadas a oportunidades ativas e que a sincronização de inscrições funcione corretamente.
     */
    function testOpportunityDeletePhaseIntermediary(): void
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
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::documentary)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->save()
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $phases = $opportunity->allPhases;

        $evaluations = [];
        $dataCollections = [];

        foreach ($phases as $phase) {
            if ($phase->isFirstPhase || $phase->isDataCollection) {
                $dataCollections[] = $phase;
            }
            if ($phase->evaluationMethodConfiguration) {
                $evaluations[] = $phase->evaluationMethodConfiguration;
            }
        }

        $this->assertCount(3, $evaluations, 'Garantindo que existam 3 fases de avaliação');
        $this->assertCount(2, $dataCollections, 'Garantindo que existam 2 fases de coleta de dados');

        $app = $this->app;

        $fase2_id = $evaluations[0]->id;
        $fase3_id = $evaluations[1]->id;
        $fase5_id = $evaluations[2]->id;
        $fase4_id = $dataCollections[1]->id;

        // Deleta a fase de coleta 2 (Opp 4) - Soft delete (status = -10)
        $fase4 = $app->repo('Opportunity')->find($fase4_id);
        $this->assertNotNull($fase4, 'Garantindo que a fase de coleta 4 exista antes da exclusão');
        $fase4->delete(true);
        
        // Verifica que a Opp foi soft-deleted
        $opp_deletada = $app->repo('Opportunity')->find($fase4_id);
        $this->assertNotNull($opp_deletada, 'Garantindo que a oportunidade ainda exista no banco');
        $this->assertEquals(-10, $opp_deletada->status, 'Garantindo que a oportunidade esteja com status deletado (soft delete)');
        
        // Verifica que a EMC ainda existe (pois o soft delete não dispara CASCADE)
        $emc_restante = $app->repo('EvaluationMethodConfiguration')->find($fase5_id);
        $this->assertNotNull($emc_restante, 'Garantindo que a EMC ainda exista após soft delete da oportunidade');
        
        // Verifica que as outras EMCs ainda existem e estão vinculadas a oportunidades ativas
        $fase2_restante = $app->repo('EvaluationMethodConfiguration')->find($fase2_id);
        $fase3_restante = $app->repo('EvaluationMethodConfiguration')->find($fase3_id);
        
        $this->assertNotNull($fase2_restante, 'Garantindo que a fase 2 (avaliação) ainda exista');
        $this->assertNotNull($fase3_restante, 'Garantindo que a fase 3 (avaliação) ainda exista');
        
        // Verifica que as EMCs estão vinculadas a oportunidades ativas (status > -10)
        $opp_fase2 = $fase2_restante->opportunity;
        $opp_fase3 = $fase3_restante->opportunity;
        
        $this->assertGreaterThan(-10, $opp_fase2->status, 'Garantindo que a oportunidade da fase 2 esteja ativa');
        $this->assertGreaterThan(-10, $opp_fase3->status, 'Garantindo que a oportunidade da fase 3 esteja ativa');
        
        // Verifica que o nextPhase e previousPhase pulam fases deletadas
        $fase1 = $opportunity->firstPhase;
        $next_da_fase1 = $fase1->nextPhase;
        $this->assertNotNull($next_da_fase1, 'Garantindo que a próxima fase após a fase 1 não seja nula');
        $this->assertGreaterThan(-10, $next_da_fase1->status, 'Garantindo que a próxima fase após a fase 1 esteja ativa (não deletada)');
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

    /**
     * Testa se o número sequencial é atribuído ao adicionar avaliadores
     */
    function testCommitteeSequentialNumberIsAssignedOnAdd(): void
    {
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
                ->addValuers(3, 'committee 1')
                ->done()
            ->refresh()
            ->getInstance();

        $evalPhase = $opportunity->evaluationMethodConfiguration;
        $this->assertNotNull($evalPhase, 'Garantindo que a fase de avaliação exista');

        $relations = $evalPhase->getAgentRelations();
        $this->assertCount(3, $relations, 'Garantindo que existam 3 avaliadores');

        $numbers = [];
        foreach ($relations as $relation) {
            $number = $relation->getCommitteeSequentialNumber();
            $this->assertNotNull($number, 'Garantindo que cada avaliador tenha um número sequencial');
            $this->assertGreaterThan(0, $number, 'Garantindo que o número seja maior que zero');
            $numbers[] = $number;
        }

        $this->assertEquals([1, 2, 3], $numbers, 'Garantindo que os números sejam sequenciais 1, 2, 3');
    }

    /**
     * Testa se o número do avaliador é igual em todas as fases de avaliação
     */
    function testCommitteeSequentialNumberIsConsistentAcrossPhases(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        // Cria agentes manualmente para reusar entre fases
        $joao = $this->agentDirector->createAgent($this->userDirector->createUser());
        $joao->name = 'João';
        $joao->save(true);
        
        $maria = $this->agentDirector->createAgent($this->userDirector->createUser());
        $maria->name = 'Maria';
        $maria->save(true);
        
        $pedro = $this->agentDirector->createAgent($this->userDirector->createUser());
        $pedro->name = 'Pedro';
        $pedro->save(true);
        
        $caio = $this->agentDirector->createAgent($this->userDirector->createUser());
        $caio->name = 'Caio';
        $caio->save(true);
        
        $ana = $this->agentDirector->createAgent($this->userDirector->createUser());
        $ana->name = 'Ana';
        $ana->save(true);

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
                ->addValuer('committee 1', 'João', $joao)
                    ->done()
                ->addValuer('committee 1', 'Maria', $maria)
                    ->done()
                ->addValuer('committee 1', 'Pedro', $pedro)
                    ->done()
                ->done()
            ->save()
            ->refresh()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('committee 1', 'Pedro', $pedro)
                    ->done()
                ->addValuer('committee 1', 'Caio', $caio)
                    ->done()
                ->addValuer('committee 1', 'Ana', $ana)
                    ->done()
                ->done()
            ->refresh()
            ->getInstance();

        $phases = $opportunity->allPhases;
        $evalPhases = array_values(array_filter($phases, fn($p) => $p->evaluationMethodConfiguration !== null));

        $this->assertCount(2, $evalPhases, 'Garantindo que existam 2 fases de avaliação');

        // Primeira fase: João=1, Maria=2, Pedro=3
        $phase1Relations = $evalPhases[0]->evaluationMethodConfiguration->getAgentRelations();
        $this->assertCount(3, $phase1Relations, 'Garantindo que a fase 1 tenha 3 avaliadores');

        $expectedPhase1 = ['João' => 1, 'Maria' => 2, 'Pedro' => 3];
        foreach ($phase1Relations as $relation) {
            $name = $relation->agent->name;
            $expectedNumber = $expectedPhase1[$name] ?? null;
            $this->assertNotNull($expectedNumber, "Garantindo que {$name} esteja na fase 1");
            $this->assertEquals($expectedNumber, $relation->getCommitteeSequentialNumber(), "Garantindo que {$name} tenha o número {$expectedNumber} na fase 1");
        }

        // Segunda fase: Pedro=3 (reuso), Caio=4, Ana=5
        $phase2Relations = $evalPhases[1]->evaluationMethodConfiguration->getAgentRelations();
        $this->assertCount(3, $phase2Relations, 'Garantindo que a fase 2 tenha 3 avaliadores');

        $expectedPhase2 = ['Pedro' => 3, 'Caio' => 4, 'Ana' => 5];
        foreach ($phase2Relations as $relation) {
            $name = $relation->agent->name;
            $expectedNumber = $expectedPhase2[$name] ?? null;
            $this->assertNotNull($expectedNumber, "Garantindo que {$name} esteja na fase 2");
            $this->assertEquals($expectedNumber, $relation->getCommitteeSequentialNumber(), "Garantindo que {$name} tenha o número {$expectedNumber} na fase 2");
        }
    }

    /**
     * Testa se ao deletar um avaliador e adicionar outro, o número segue a sequência
     */
    function testCommitteeSequentialNumberContinuesAfterDeletion(): void
    {
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
                ->addValuer('committee 1', 'João')
                    ->done()
                ->addValuer('committee 1', 'Maria')
                    ->done()
                ->addValuer('committee 1', 'Pedro')
                    ->done()
                ->done()
            ->refresh()
            ->getInstance();

        $evalPhase = $opportunity->evaluationMethodConfiguration;
        $relations = $evalPhase->getAgentRelations();
        $this->assertCount(3, $relations, 'Garantindo que existam 3 avaliadores inicialmente');

        // Encontra e deleta o avaliador Maria (número 2)
        $mariaRelation = null;
        foreach ($relations as $relation) {
            if ($relation->agent->name === 'Maria') {
                $mariaRelation = $relation;
                break;
            }
        }
        $this->assertNotNull($mariaRelation, 'Garantindo que Maria foi encontrada');
        $this->assertEquals(2, $mariaRelation->getCommitteeSequentialNumber(), 'Garantindo que Maria tenha o número 2');

        $mariaRelation->delete(true);

        // Atualiza e verifica que só restam 2 avaliadores
        $evalPhase = $evalPhase->refreshed();
        $relations = $evalPhase->getAgentRelations();
        $this->assertCount(2, $relations, 'Garantindo que restem 2 avaliadores após deletar Maria');

        // Adiciona um novo avaliador (Sandro)
        $sandroUser = $this->userDirector->createUser();
        $sandroUser->profile->name = 'Sandro';
        $sandroUser->profile->save(true);
        $newValuer = $evalPhase->createAgentRelation(
            agent: $sandroUser->profile,
            group: 'committee 1',
            has_control: true
        );
        $newValuer->save(true);

        // Verifica se o novo avaliador recebeu o número 4 (não o 2)
        $this->assertEquals(4, $newValuer->getCommitteeSequentialNumber(), 'Garantindo que o novo avaliador receba o número 4, não o 2');

        // Verifica que os números existentes são 1, 3, 4
        $evalPhase = $evalPhase->refreshed();
        $relations = $evalPhase->getAgentRelations();
        $numbers = [];
        foreach ($relations as $relation) {
            $numbers[$relation->agent->name] = $relation->getCommitteeSequentialNumber();
        }

        $this->assertEquals(['João' => 1, 'Pedro' => 3, 'Sandro' => 4], $numbers, 'Garantindo que os números sejam 1, 3, 4 após substituição');
    }

    /**
     * Testa se ao deletar todos os avaliadores, os números são removidos
     */
    function testCommitteeSequentialNumbersAreRemovedOnDeleteAll(): void
    {
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
                ->addValuers(3, 'committee 1')
                ->done()
            ->refresh()
            ->getInstance();

        $evalPhase = $opportunity->evaluationMethodConfiguration;
        $relations = $evalPhase->getAgentRelations();
        $this->assertCount(3, $relations, 'Garantindo que existam 3 avaliadores inicialmente');

        // Deleta todos os avaliadores
        foreach ($relations as $relation) {
            $relation->delete(true);
        }

        // Verifica que não existem mais avaliadores
        $evalPhase = $evalPhase->refreshed();
        $relations = $evalPhase->getAgentRelations();
        $this->assertCount(0, $relations, 'Garantindo que não existam avaliadores após deletar todos');

        // Verifica no banco se os números foram removidos
        $conn = $this->app->em->getConnection();
        $count = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM agent_relation 
             WHERE object_type = 'MapasCulturais\\Entities\\EvaluationMethodConfiguration' 
             AND object_id = ?",
            [$evalPhase->id]
        );
        $this->assertEquals(0, $count, 'Garantindo que não existam registros de agent_relation no banco');
    }

    /**
     * Testa se ao deletar um avaliador de uma fase, as outras fases mantêm o número dele
     * e se ele for re-adicionado, recebe o mesmo número
     */
    function testCommitteeSequentialNumberPersistsInOtherPhasesAfterDeletion(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        // Cria agentes manualmente para reusar entre fases
        $joao = $this->agentDirector->createAgent($this->userDirector->createUser());
        $joao->name = 'João';
        $joao->save(true);

        $pedro = $this->agentDirector->createAgent($this->userDirector->createUser());
        $pedro->name = 'Pedro';
        $pedro->save(true);

        $caio = $this->agentDirector->createAgent($this->userDirector->createUser());
        $caio->name = 'Caio';
        $caio->save(true);

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
                ->addValuer('committee 1', 'João', $joao)
                    ->done()
                ->addValuer('committee 1', 'Pedro', $pedro)
                    ->done()
                ->done()
            ->save()
            ->refresh()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('committee 1', 'João', $joao)
                    ->done()
                ->addValuer('committee 1', 'Pedro', $pedro)
                    ->done()
                ->addValuer('committee 1', 'Caio', $caio)
                    ->done()
                ->done()
            ->refresh()
            ->getInstance();

        $phases = $opportunity->allPhases;
        $evalPhases = array_values(array_filter($phases, fn($p) => $p->evaluationMethodConfiguration !== null));
        $this->assertCount(2, $evalPhases, 'Garantindo que existam 2 fases de avaliação');

        $phase1 = $evalPhases[0]->evaluationMethodConfiguration;
        $phase2 = $evalPhases[1]->evaluationMethodConfiguration;

        // Verifica números iniciais
        $phase1Relations = $phase1->getAgentRelations();
        $phase2Relations = $phase2->getAgentRelations();

        $this->assertCount(2, $phase1Relations, 'Garantindo que a fase 1 tenha 2 avaliadores');
        $this->assertCount(3, $phase2Relations, 'Garantindo que a fase 2 tenha 3 avaliadores');

        // Pedro tem número 2 em ambas as fases
        $pedroPhase1 = null;
        foreach ($phase1Relations as $relation) {
            if ($relation->agent->name === 'Pedro') {
                $pedroPhase1 = $relation;
                break;
            }
        }
        $this->assertNotNull($pedroPhase1, 'Garantindo que Pedro está na fase 1');
        $this->assertEquals(2, $pedroPhase1->getCommitteeSequentialNumber(), 'Garantindo que Pedro tenha número 2 na fase 1');

        $pedroPhase2 = null;
        foreach ($phase2Relations as $relation) {
            if ($relation->agent->name === 'Pedro') {
                $pedroPhase2 = $relation;
                break;
            }
        }
        $this->assertNotNull($pedroPhase2, 'Garantindo que Pedro está na fase 2');
        $this->assertEquals(2, $pedroPhase2->getCommitteeSequentialNumber(), 'Garantindo que Pedro tenha número 2 na fase 2');

        // Deleta Pedro da fase 1
        $pedroPhase1->delete(true);

        // Verifica que Pedro foi removido da fase 1
        $phase1 = $phase1->refreshed();
        $phase1Relations = $phase1->getAgentRelations();
        $this->assertCount(1, $phase1Relations, 'Garantindo que a fase 1 tenha 1 avaliador após deletar Pedro');

        // Verifica que Pedro ainda existe na fase 2 com o mesmo número
        $phase2 = $phase2->refreshed();
        $phase2Relations = $phase2->getAgentRelations();
        $this->assertCount(3, $phase2Relations, 'Garantindo que a fase 2 ainda tenha 3 avaliadores');

        $pedroPhase2AfterDelete = null;
        foreach ($phase2Relations as $relation) {
            if ($relation->agent->name === 'Pedro') {
                $pedroPhase2AfterDelete = $relation;
                break;
            }
        }
        $this->assertNotNull($pedroPhase2AfterDelete, 'Garantindo que Pedro ainda está na fase 2 após deleção da fase 1');
        $this->assertEquals(2, $pedroPhase2AfterDelete->getCommitteeSequentialNumber(), 'Garantindo que Pedro mantém o número 2 na fase 2');

        // Re-adiciona Pedro na fase 1 - deve receber o mesmo número 2
        $newPedroRelation = $phase1->createAgentRelation($pedro, 'committee 1', true);
        $newPedroRelation->save(true);

        $this->assertEquals(2, $newPedroRelation->getCommitteeSequentialNumber(), 'Garantindo que Pedro receba o mesmo número 2 ao ser re-adicionado na fase 1');

        // Verifica que a fase 1 agora tem João (1) e Pedro (2) novamente
        $phase1 = $phase1->refreshed();
        $phase1Relations = $phase1->getAgentRelations();
        $this->assertCount(2, $phase1Relations, 'Garantindo que a fase 1 tenha 2 avaliadores após re-adicionar Pedro');

        $numbers = [];
        foreach ($phase1Relations as $relation) {
            $numbers[$relation->agent->name] = $relation->getCommitteeSequentialNumber();
        }
        $this->assertEquals(['João' => 1, 'Pedro' => 2], $numbers, 'Garantindo que os números na fase 1 estejam corretos após re-adicionar Pedro');
    }
}
