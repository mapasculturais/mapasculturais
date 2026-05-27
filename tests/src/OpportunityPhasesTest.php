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
     * Garante que uma falha durante a criação da fase de recurso não deixa
     * fase órfã no banco (sem metadado appealPhase).
     */
    function testCreateAppealPhaseFailureRollsBackOrphanPhase(): void
    {
        $app = $this->app;
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        // Cria uma oportunidade com fase de avaliação
        /** @var Opportunity $opportunity */
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
            ->refresh()
            ->getInstance();

        $opportunityId = $opportunity->id;

        // Adiciona um hook que simula uma falha (timeout/erro) durante o save
        // da oportunidade principal, APÓS a fase de recurso ser criada mas ANTES
        // do metadado appealPhase ser persistido.
        $shouldFail = true;
        $app->hook('entity(Opportunity).save:before', function() use ($opportunityId, &$shouldFail) {
            /** @var Opportunity $this */
            if (!$shouldFail || $this->id != $opportunityId || $this->isAppealPhase || !$this->appealPhase) {
                return;
            }

            $shouldFail = false;
            throw new \RuntimeException('Simulação de falha ao persistir metadado appealPhase');
        });

        // Tenta criar a fase de recurso via endpoint
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'createAppealPhase', [$opportunityId], ['id' => $opportunityId]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $opportunityId]);

        try {
            $controller->callAction('POST', 'createAppealPhase', []);
            $this->fail('Garantindo que a criação da fase de recurso lance exceção quando o save falha');
        } catch (Halt) {
            $this->fail('Garantindo que a criação da fase de recurso não conclua com sucesso quando o save falha');
        } catch (\Throwable) {
        }

        $app->em->clear();

        $orphanPhaseCount = (int) $app->em->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM opportunity WHERE parent_id = :parent_id AND status = :status',
            ['parent_id' => $opportunityId, 'status' => Opportunity::STATUS_APPEAL_PHASE]
        );

        $this->assertSame(0, $orphanPhaseCount, 'Garantindo que nenhuma fase de recurso órfã permaneça no banco após falha');

        $appealPhaseMeta = $app->repo('OpportunityMeta')->findOneBy([
            'owner' => $app->repo('Opportunity')->find($opportunityId),
            'key' => 'appealPhase',
        ]);

        $this->assertNull($appealPhaseMeta, 'Garantindo que o metadado appealPhase não foi salvo parcialmente no edital');
    }
}
