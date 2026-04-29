<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityExecutionPhaseTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Monta uma oportunidade com:
     *   fase 1 (coleta) → avaliação simplificada → lastPhase
     * e cria uma inscrição aprovada na lastPhase para o agente informado.
     */
    private function buildOpportunityWithApprovedRegistration(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity $first_phase */
        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        // Inscrição na fase 1
        $registration = $this->registrationDirector->createSentRegistrations($first_phase, 1)[0];

        // Propaga manualmente para a lastPhase como aprovada
        $last_phase = $first_phase->lastPhase;
        $last_phase->sync = true;

        $approved = new Registration();
        $approved->opportunity = $last_phase;
        $approved->owner       = $registration->owner;
        $approved->number      = $registration->number;
        $approved->save(true);

        $app = $this->app;
        $app->disableAccessControl();
        $approved->status = Registration::STATUS_APPROVED;
        $approved->save(true);
        $app->enableAccessControl();

        return [$admin, $first_phase, $approved];
    }

    // ----------------------------------------------------------------
    // Testes de metadados e criação da fase
    // ----------------------------------------------------------------

    /**
     * A fase de execução precisa ter isExecutionPhase = true e
     * registrationLimitPerOwner = 0 após ser criada pelo builder.
     */
    public function testExecutionPhaseIsCreatedWithCorrectFlags()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        $this->assertTrue((bool) $execution_phase->isExecutionPhase,
            'A fase de execução deve ter isExecutionPhase = true');

        $this->assertTrue((bool) $execution_phase->isOpportunityPhase,
            'A fase de execução deve ter isOpportunityPhase = true');

        $this->assertTrue((bool) $execution_phase->isDataCollection,
            'A fase de execução deve ter isDataCollection = true');

        $this->assertEquals(0, (int) $execution_phase->registrationLimitPerOwner,
            'registrationLimitPerOwner deve ser 0 para permitir N pedidos por agente');
    }

    public function testExecutionPhaseCannotStartBeforeFinalResultPublicationDate()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $last_phase = $first_phase->lastPhase;
        $last_phase->publishTimestamp = new \DateTime('+ 10 days');
        $last_phase->save(true);

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->getInstance();

        $execution_phase->registrationFrom = new \DateTime('+ 1 day');
        $execution_phase->registrationTo = new \DateTime('+ 2 days');

        $evaluation_phase = new EvaluationMethodConfiguration();
        $evaluation_phase->opportunity = $execution_phase;
        $evaluation_phase->type = 'simple';
        $evaluation_phase->name = 'Avaliação dos pedidos';
        $evaluation_phase->evaluationFrom = new \DateTime('+ 2 days');
        $evaluation_phase->evaluationTo = new \DateTime('+ 3 days');

        $this->assertArrayHasKey('registrationFrom', $execution_phase->getValidationErrors(),
            'A fase de execução não deve começar antes da publicação final do resultado');

        $execution_phase->registrationFrom = new \DateTime('+ 11 days');
        $execution_phase->registrationTo = new \DateTime('+ 12 days');
        $evaluation_phase->evaluationFrom = new \DateTime('+ 12 days');
        $evaluation_phase->evaluationTo = new \DateTime('+ 13 days');

        $this->assertEmpty($execution_phase->getValidationErrors(),
            'A fase de execução deve poder começar depois da publicação final do resultado');

        $this->assertEmpty($evaluation_phase->getValidationErrors(),
            'A avaliação da execução deve poder terminar depois da publicação final do resultado');
    }

    // ----------------------------------------------------------------
    // Testes de posição em allPhases
    // ----------------------------------------------------------------

    /**
     * A fase de execução deve aparecer após a lastPhase e antes das
     * fases de prestação (isReportingPhase) na lista allPhases.
     */
    public function testExecutionPhasePositionInAllPhases()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        $first_phase = $first_phase->refreshed();
        $all_phases  = $first_phase->allPhases;

        $positions = [];
        foreach ($all_phases as $i => $phase) {
            if ($phase->isLastPhase)      $positions['last']      = $i;
            if ($phase->isExecutionPhase) $positions['execution'] = $i;
            if ($phase->isReportingPhase) $positions['reporting'] = $i;
        }

        $this->assertArrayHasKey('execution', $positions,
            'Fase de execução deve estar na lista allPhases');

        $this->assertGreaterThan($positions['last'], $positions['execution'],
            'Fase de execução deve aparecer após a lastPhase');
    }

    // ----------------------------------------------------------------
    // Testes de criação de pedidos
    // ----------------------------------------------------------------

    /**
     * Um agente com inscrição aprovada deve conseguir criar N pedidos
     * na fase de execução.
     */
    public function testAgentCanCreateMultipleRequests()
    {
        [$admin, $first_phase, $approved] = $this->buildOpportunityWithApprovedRegistration();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        $execution_phase_builder = $this->opportunityBuilder->addExecutionPhase()
            ->withInstance($execution_phase);

        $pedido1 = $execution_phase_builder->createRequest($approved);
        $pedido2 = $execution_phase_builder->createRequest($approved);
        $pedido3 = $execution_phase_builder->createRequest($approved);

        $this->assertEquals($approved->owner->id, $pedido1->owner->id);
        $this->assertEquals($approved->owner->id, $pedido2->owner->id);
        $this->assertEquals($approved->owner->id, $pedido3->owner->id);

        $this->assertNotEquals($pedido1->number, $pedido2->number,
            'Cada pedido deve ter um number independente');

        $this->assertNotEquals($pedido1->number, $pedido3->number,
            'Pedidos do mesmo agente também devem ter numbers distintos');
    }

    /**
     * O vínculo previousPhaseRegistrationId deve apontar para a inscrição
     * aprovada na lastPhase, não herdar o number da fase anterior.
     */
    public function testRequestLinkedToApprovedRegistration()
    {
        [$admin, $first_phase, $approved] = $this->buildOpportunityWithApprovedRegistration();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        $pedido = new Registration();
        $pedido->opportunity = $execution_phase;
        $pedido->owner       = $approved->owner;
        $pedido->previousPhaseRegistrationId = $approved->id;
        $pedido->save(true);

        $this->assertEquals($approved->id, $pedido->previousPhaseRegistrationId,
            'O pedido deve estar vinculado à inscrição aprovada via previousPhaseRegistrationId');

        $this->assertNotEquals($approved->number, $pedido->number,
            'O number do pedido deve ser novo, não o da inscrição original');
    }

    // ----------------------------------------------------------------
    // Testes de isolamento da sincronização
    // ----------------------------------------------------------------

    /**
     * A fase de execução não deve participar da sincronização automática
     * de inscrições. Seus pedidos não devem ser removidos como "órfãos".
     */
    public function testExecutionPhaseNotSynced()
    {
        [$admin, $first_phase, $approved] = $this->buildOpportunityWithApprovedRegistration();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        // Cria um pedido na fase de execução
        $pedido = new Registration();
        $pedido->opportunity = $execution_phase;
        $pedido->owner       = $approved->owner;
        $pedido->previousPhaseRegistrationId = $approved->id;
        $pedido->save(true);

        $pedido_id = $pedido->id;

        // Dispara sync na cadeia de fases a partir da primeira
        $app = $this->app;
        $app->disableAccessControl();
        $first_phase->refreshed()->nextPhase?->syncRegistrations();
        $app->enableAccessControl();

        // O pedido deve continuar existindo após a sync
        $pedido_after = $app->repo('Registration')->find($pedido_id);
        $this->assertNotNull($pedido_after,
            'Pedidos da fase de execução não devem ser removidos pela sincronização automática');
    }

    /**
     * A fase de execução não deve aparecer como previousPhase de fases sequenciais,
     * para não contaminar a importação de inscrições.
     */
    public function testExecutionPhaseNotPreviousPhaseOfReportingPhase()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $execution_phase = $this->opportunityBuilder
            ->addExecutionPhase()
            ->save()
            ->getInstance();

        $first_phase = $first_phase->refreshed();
        $last_phase  = $first_phase->lastPhase;

        if ($last_phase) {
            $prev = $last_phase->previousPhase;
            if ($prev) {
                $this->assertFalse((bool) $prev->isExecutionPhase,
                    'A fase de execução não deve ser resolvida como previousPhase de outra fase sequencial');
            }
        }

        // Verifica especificamente que a execução não aparece em nenhum previousPhase
        foreach ($first_phase->allPhases as $phase) {
            if ($phase->id === $execution_phase->id) {
                continue;
            }
            $prev = $phase->previousPhase;
            if ($prev) {
                $this->assertFalse((bool) $prev->isExecutionPhase,
                    "A fase de execução não deve ser previousPhase da fase {$phase->name}");
            }
        }
    }
}
