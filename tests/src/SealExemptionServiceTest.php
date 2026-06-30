<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Seal;
use SealExemption\ProponentAgentResolver;
use SealExemption\SealExemptionService;
use SealExemption\SealExemptionVerifier;
use Tests\Abstract\TestCase;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Tests for SealExemptionService reflecting the actual implementation.
 */
class SealExemptionServiceTest extends TestCase
{
    use AgentDirector;
    use OpportunityBuilder;
    use SealDirector;
    use UserDirector;

    private SealExemptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SealExemptionService(
            new ProponentAgentResolver(),
            new SealExemptionVerifier()
        );
    }

    /**
     * Helper: create a project-based opportunity with one evaluation phase
     * (non-technical) and the given seal exemption config.
     */
    private function createOpportunityWithSealConfig(array $sealIds, ?string $label = null): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $project = new Project();
        $project->name = 'Test Project';
        $project->type = 1;
        $project->owner = $owner;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($owner, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $emc = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $opportunity = $emc->opportunity;

        $emc->sealExemptionConfig = (object) [
            'seals' => $sealIds,
            'label' => $label ?? 'Isento por selos válidos',
        ];
        $emc->save(true);

        return $opportunity;
    }

    /**
     * Helper: create a registration in the evaluation phase with a previous
     * phase registration id set (so the hook guard considers it a phase entry).
     */
    private function createPhaseRegistration(Opportunity $opportunity, Agent $owner): Registration
    {
        $opportunity->registerRegistrationMetadata();

        $registration = new Registration();
        $registration->opportunity = $opportunity;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->previousPhaseRegistrationId = 999999;
        $registration->save(true);

        return $registration;
    }

    /**
     * Helper: create a seal and relate it to an agent, forcing computed_status.
     */
    private function createFullyValidSealForAgent(Agent $agent, Agent $applyingAgent): Seal
    {
        $seal = $this->sealDirector->createSeal($applyingAgent);
        $agent->createSealRelation($seal, true, true, $applyingAgent);

        App::i()->em->getConnection()->executeQuery(
            "UPDATE seal_relation
             SET computed_status = 'fully_valid'
             WHERE object_type = :object_type
               AND object_id = :agent_id
               AND seal_id = :seal_id",
            [
                'object_type' => 'MapasCulturais\Entities\Agent',
                'agent_id'    => $agent->id,
                'seal_id'     => $seal->id,
            ]
        );

        return $seal;
    }

    /**
     * Helper: create a seal and relate it to an agent with an invalid status.
     */
    private function createInvalidSealForAgent(Agent $agent, Agent $applyingAgent): Seal
    {
        $seal = $this->sealDirector->createSeal($applyingAgent);
        $agent->createSealRelation($seal, true, true, $applyingAgent);

        App::i()->em->getConnection()->executeQuery(
            "UPDATE seal_relation
             SET computed_status = 'invalid'
             WHERE object_type = :object_type
               AND object_id = :agent_id
               AND seal_id = :seal_id",
            [
                'object_type' => 'MapasCulturais\Entities\Agent',
                'agent_id'    => $agent->id,
                'seal_id'     => $seal->id,
            ]
        );

        return $seal;
    }

    public function testApplyExemptionCheckGrantsExemptionWhenAllSealsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id], 'Test Label');
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();

        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
        $this->assertNotNull($registration->sealExemptionTimestamp);
    }

    public function testApplyExemptionCheckDoesNothingWhenSealsInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createInvalidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();

        $this->assertSame(Registration::STATUS_DRAFT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertNull($registration->sealExemptionTimestamp);
    }

    public function testApplyExemptionCheckSetsAgentMissingWhenProponentNotResolved(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);

        // PJ requires collective relation; without it the resolver returns null.
        $opportunity->firstPhase->useAgentRelationColetivo = 'required';
        $opportunity->firstPhase->save(true);

        $registration = $this->createPhaseRegistration($opportunity, $owner);
        $registration->proponentType = 'Pessoa Jurídica';
        $registration->save(true);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $registration = $registration->refreshed();

        $this->assertSame('agent_missing', $registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_DRAFT, $registration->status);
    }

    public function testApplyExemptionCheckIsIdempotentForAlreadyGranted(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;

        $this->service->applyExemptionCheck($registration, $config);
        $firstTimestamp = $registration->refreshed()->sealExemptionTimestamp;

        $this->service->applyExemptionCheck($registration, $config);
        $secondTimestamp = $registration->refreshed()->sealExemptionTimestamp;

        $this->assertEquals($firstTimestamp, $secondTimestamp);
    }

    public function testApplyExemptionCheckIsIdempotentForAgentMissing(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $opportunity->firstPhase->useAgentRelationColetivo = 'required';
        $opportunity->firstPhase->save(true);

        $registration = $this->createPhaseRegistration($opportunity, $owner);
        $registration->proponentType = 'Pessoa Jurídica';
        $registration->save(true);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;

        $this->service->applyExemptionCheck($registration, $config);
        $this->assertSame('agent_missing', $registration->refreshed()->sealExemptionStatus);

        // Manually reset status to see that the service skips reprocessing.
        $registration->sealExemptionStatus = null;
        $registration->save(true);

        $this->service->applyExemptionCheck($registration, $config);
        $this->assertSame('agent_missing', $registration->refreshed()->sealExemptionStatus);
    }

    public function testSnapshotMetadataIsWrittenWhenGranted(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id], 'Snapshot Label');
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $snapshot = $registration->refreshed()->sealExemptionSnapshot;
        $this->assertNotNull($snapshot);

        $decoded = json_decode(is_string($snapshot) ? $snapshot : json_encode($snapshot), true);
        $this->assertSame($opportunity->evaluationMethodConfiguration->id, $decoded['emc_id']);
        $this->assertSame([$seal->id], $decoded['seal_ids']);
        $this->assertSame('Snapshot Label', $decoded['label']);
        $this->assertSame($owner->id, $decoded['agent_id']);
    }

    public function testManualApprovalGrantsValidatorSealsAfterSelection(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->assertCount(0, $owner->getSealRelations());

        $app = App::i();
        $app->disableAccessControl();
        try {
            $registration->setStatusToApproved(true);
        } finally {
            $app->enableAccessControl();
        }

        $owner = $owner->refreshed();
        $relations = $owner->getSealRelations();
        $this->assertCount(1, $relations);
        $this->assertSame($seal->id, $relations[0]->seal->id);
    }

    public function testManualApprovalDoesNotReGrantSealsForAlreadyExempt(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        // Re-approving an already-exempt registration must not create extra relations.
        $relationCountBefore = count($owner->refreshed()->getSealRelations());
        $registration->refreshed()->setStatusToApproved(true);
        $relationCountAfter = count($owner->refreshed()->getSealRelations());

        $this->assertSame($relationCountBefore, $relationCountAfter);
    }
}
