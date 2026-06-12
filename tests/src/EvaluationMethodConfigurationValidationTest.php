<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use Tests\Abstract\TestCase;
use Tests\Enums\EvaluationMethods;
use Tests\Factories\RequestFactory;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;
use Tests\Builders\PhasePeriods\Past;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;

/**
 * Teste de validacao da integridade entre secoes e criterios de avaliacao.
 * 
 * Garante que o backend rejeita dados inconsistentes via API PATCH:
 * - Critérios sem seção associada (orfãos)
 * - Critérios com campos obrigatórios vazios
 * - Seções sem critérios (quando há critérios)
 * 
 * Issue: Critério c-1779469976899 no edital 192 ficou orfao porque sua secao
 * s-1779469717745 nunca existiu nas sections configuradas.
 */
class EvaluationMethodConfigurationValidationTest extends TestCase
{
    use UserDirector,
        OpportunityBuilder,
        RegistrationDirector;

    protected RequestFactory $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestFactory = new RequestFactory();
    }

    /**
     * Cria oportunidade com avaliacao tecnica
     */
    private function setupOpportunityWithTechnicalPhase(): EvaluationMethodConfiguration
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $opportunity = $opportunity->refreshed();
        $config = null;
        foreach ($opportunity->allPhases as $phase) {
            if ($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type->id === 'technical') {
                $config = $phase->evaluationMethodConfiguration;
                break;
            }
        }

        $this->assertNotNull($config, 'Config de avaliacao tecnica deve existir');
        return $config;
    }

    /**
     * Cenario 1: PATCH com criterio tecnico apontando para secao INEXISTENTE (orfao)
     * 
     * Esperado: Rejeitar com status 400
     */
    function testRejectsTechnicalCriteriaWithNonExistentSectionId()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $validSectionId = 's-valid-' . time();
        $validCriterionId = 'c-valid-' . time();
        $orphanCriterionId = 'c-orphan-' . time();
        $ghostSectionId = 's-ghost-' . time();

        // Configura dados validos inicialmente
        $patchValid = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [
                    ['id' => $validSectionId, 'name' => 'Secao Valida']
                ],
                'criteria' => [
                    [
                        'id' => $validCriterionId,
                        'sid' => $validSectionId,
                        'title' => 'Criterio Valido',
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ]
                ]
            ]
        );
        $this->assertStatus200($patchValid, 'PATCH inicial valido deve retornar 200');

        // Tenta adicionar criterio orfao - DEVE SER REJEITADO
        $patchOrphan = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'criteria' => [
                    [
                        'id' => $validCriterionId,
                        'sid' => $validSectionId,
                        'title' => 'Criterio Valido',
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ],
                    [
                        'id' => $orphanCriterionId,
                        'sid' => $ghostSectionId, // <-- SECAO NAO EXISTE
                        'title' => 'Criterio Orfao',
                        'min' => 0,
                        'max' => 5,
                        'weight' => 1
                    ]
                ]
            ]
        );

        // DEVE rejeitar com 400
        $this->assertStatus400(
            $patchOrphan,
            'Sistema deve REJEITAR criterio com sid de secao inexistente (status 400)'
        );

        // Verifica que NAO foi salvo no banco
        App::i()->em->clear();
        $config = App::i()->repo('EvaluationMethodConfiguration')->find($technicalConfig->id);
        
        $savedOrphan = null;
        foreach ($config->criteria ?? [] as $c) {
            if ($c->id === $orphanCriterionId) {
                $savedOrphan = $c;
                break;
            }
        }
        
        $this->assertNull($savedOrphan, 'Criterio orfao NAO deve ser salvo no banco');
    }

    /**
     * Cenario 2: PATCH com sections vazias mas criteria preenchido
     * 
     * Esperado: Rejeitar com status 400
     */
    function testRejectsTechnicalCriteriaWithEmptySections()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $criterionId = 'c-orphan2-' . time();
        $ghostSectionId = 's-ghost2-' . time();

        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [], // <-- VAZIO
                'criteria' => [
                    [
                        'id' => $criterionId,
                        'sid' => $ghostSectionId,
                        'title' => 'Criterio Sem Secao',
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ]
                ]
            ]
        );

        // DEVE rejeitar com 400
        $this->assertStatus400(
            $patch,
            'Sistema deve REJEITAR sections=[] com criteria preenchido (status 400)'
        );
    }

    /**
     * Cenario 3: PATCH com critério faltando campos obrigatorios
     * 
     * Campos obrigatorios: title, max, weight
     * Esperado: Rejeitar com status 400
     */
    function testRejectsTechnicalCriteriaWithMissingRequiredFields()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $sectionId = 's-missing-' . time();
        
        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [
                    ['id' => $sectionId, 'name' => 'Secao Teste']
                ],
                'criteria' => [
                    [
                        'id' => 'c-missing-' . time(),
                        'sid' => $sectionId,
                        // 'title' => FALTANDO!
                        'min' => 0,
                        // 'max' => FALTANDO!
                        // 'weight' => FALTANDO!
                    ]
                ]
            ]
        );

        // DEVE rejeitar com 400
        $this->assertStatus400(
            $patch,
            'Sistema deve REJEITAR criterio sem campos obrigatorios (status 400)'
        );
    }

    /**
     * Cenario 4: PATCH com critério usando 'name' ao inves de 'title'
     * 
     * O campo obrigatorio eh 'title'. Se vier 'name', title fica vazio.
     * Esperado: Rejeitar com status 400
     */
    function testRejectsTechnicalCriteriaWithNameInsteadOfTitle()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $sectionId = 's-name-' . time();
        
        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [
                    ['id' => $sectionId, 'name' => 'Secao Teste']
                ],
                'criteria' => [
                    [
                        'id' => 'c-name-' . time(),
                        'sid' => $sectionId,
                        'name' => 'Usando name em vez de title', // <-- CAMPO ERRADO
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ]
                ]
            ]
        );

        // DEVE rejeitar com 400
        $this->assertStatus400(
            $patch,
            'Sistema deve REJEITAR criteria com "name" em vez de "title" (status 400)'
        );
    }

    /**
     * Cenario 5: PATCH valido com secoes e criterios corretos
     * 
     * Esperado: Aceitar com status 200
     */
    function testAcceptsValidCriteriaAndSections()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $sectionId = 's-valid-' . time();
        $criterionId = 'c-valid-' . time();
        
        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [
                    ['id' => $sectionId, 'name' => 'Secao Valida']
                ],
                'criteria' => [
                    [
                        'id' => $criterionId,
                        'sid' => $sectionId,
                        'title' => 'Criterio Valido',
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ]
                ]
            ]
        );

        // DEVE aceitar com 200
        $this->assertStatus200($patch, 'PATCH valido deve retornar 200');

        // Verifica que foi salvo corretamente
        App::i()->em->clear();
        $config = App::i()->repo('EvaluationMethodConfiguration')->find($technicalConfig->id);
        
        $sections = $config->sections ?? [];
        $criteria = $config->criteria ?? [];
        
        $this->assertCount(1, $sections, 'Deve ter 1 secao');
        $this->assertCount(1, $criteria, 'Deve ter 1 criterio');
        $this->assertEquals($sectionId, $sections[0]->id, 'Secao salva corretamente');
        $this->assertEquals($criterionId, $criteria[0]->id, 'Criterio salvo corretamente');
        $this->assertEquals($sectionId, $criteria[0]->sid, 'Criterio vinculado a secao correta');
    }

    /**
     * Cenario 7: Hook remove secoes sem criterios automaticamente
     * 
     * Se uma secao eh enviada sem criterios, o hook save:before remove.
     * Mas se NAO ha criterios enviados, nao deve afetar.
     */
    function testHookRemovesSectionsWithoutCriteria()
    {
        $technicalConfig = $this->setupOpportunityWithTechnicalPhase();
        
        $sectionWithCriteria = 's-with-' . time();
        $sectionWithoutCriteria = 's-without-' . time();
        
        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$technicalConfig->id],
            payload: [
                'sections' => [
                    ['id' => $sectionWithCriteria, 'name' => 'Com Criterio'],
                    ['id' => $sectionWithoutCriteria, 'name' => 'Sem Criterio']
                ],
                'criteria' => [
                    [
                        'id' => 'c-only-' . time(),
                        'sid' => $sectionWithCriteria,
                        'title' => 'Unico Criterio',
                        'min' => 0,
                        'max' => 10,
                        'weight' => 1
                    ]
                ]
            ]
        );

        $this->assertStatus200($patch, 'PATCH valido retorna 200');

        // Verifica que secao sem criterio foi removida pelo hook
        App::i()->em->clear();
        $config = App::i()->repo('EvaluationMethodConfiguration')->find($technicalConfig->id);
        
        $sections = $config->sections ?? [];
        $this->assertCount(1, $sections, 'Hook removeu secao sem criterios');
        $this->assertEquals($sectionWithCriteria, $sections[0]->id, 'Sobrou apenas secao com criterio');
    }

    /**
     * Cenario 8: PATCH qualification com secao sem criterios
     * 
     * Esperado: Rejeitar com status 400
     */
    function testRejectsQualificationSectionWithoutCriteria()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->done()
            ->addEvaluationPhase(EvaluationMethods::qualification)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $opportunity = $opportunity->refreshed();
        $config = null;
        foreach ($opportunity->allPhases as $phase) {
            if ($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type->id === 'qualification') {
                $config = $phase->evaluationMethodConfiguration;
                break;
            }
        }

        $this->assertNotNull($config, 'Config de avaliacao por qualificacao deve existir');

        $patch = $this->requestFactory->PATCH(
            controller_id: 'evaluationMethodConfiguration',
            action: 'single',
            url_params: [$config->id],
            payload: [
                'sections' => [
                    ['id' => 's-empty-' . time(), 'name' => 'Secao Vazia']
                ],
                'criteria' => []
            ]
        );

        $this->assertStatus400(
            $patch,
            'Sistema deve REJEITAR secao sem criterios em avaliacao qualification (status 400)'
        );
    }

    // ==================== HELPERS ====================

    /**
     * Wrapper para assertStatus200 que suprime output do framework
     */
    protected function assertStatus200($request, string $message = '')
    {
        ob_start();
        parent::assertStatus200($request, $message);
        ob_end_clean();
    }

    /**
     * Wrapper para assertStatus400 que suprime output do framework
     */
    protected function assertStatus400($request, string $message = '')
    {
        ob_start();
        parent::assertStatus400($request, $message);
        ob_end_clean();
    }

    /**
     * Cria inscricao e avaliacao para testar impacto
     */
    private function createRegistrationAndEvaluate(EvaluationMethodConfiguration $config, array $evaluationData): ?Registration
    {
        $registration = $this->registrationDirector->createSentRegistration($this->opportunity, data: []);
        $registration->setStatusToApproved(true);
        
        $phase = $config->opportunity;
        $phase->syncRegistrations([$registration]);
        $this->processJobs();
        
        App::i()->em->clear();
        $technicalRegistration = App::i()->repo('Registration')->findOneBy([
            'number' => $registration->number,
            'opportunity' => $phase,
        ]);

        return $technicalRegistration;
    }

    /**
     * Retorna ultima avaliacao da inscricao
     */
    private function getLastEvaluation(Registration $registration): ?RegistrationEvaluation
    {
        $evaluations = App::i()->repo('RegistrationEvaluation')->findBy([
            'registration' => $registration
        ], ['id' => 'DESC']);

        return $evaluations[0] ?? null;
    }
}
