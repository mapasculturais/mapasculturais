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
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
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
    private function createOpportunityWithSealConfig(array $sealIds): Opportunity
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

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
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

        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $config = $opportunity->evaluationMethodConfiguration->sealExemptionConfig;
        $this->service->applyExemptionCheck($registration, $config);

        $snapshot = $registration->refreshed()->sealExemptionSnapshot;
        $this->assertNotNull($snapshot);

        $decoded = json_decode(is_string($snapshot) ? $snapshot : json_encode($snapshot), true);
        $this->assertSame($opportunity->evaluationMethodConfiguration->id, $decoded['emc_id']);
        $this->assertSame([$seal->id], $decoded['seal_ids']);
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

    public function testHasActiveConfigReturnsFalseForEmptyOrMissingSeals(): void
    {
        $this->assertFalse(SealExemptionService::hasActiveConfig(null));
        $this->assertFalse(SealExemptionService::hasActiveConfig((object) ['seals' => []]));
        $this->assertFalse(SealExemptionService::hasActiveConfig(['seals' => ['', 0]]));
    }

    public function testHasActiveConfigReturnsTrueWhenSealsConfigured(): void
    {
        $this->assertTrue(SealExemptionService::hasActiveConfig((object) ['seals' => [1, 2]]));
        $this->assertTrue(SealExemptionService::hasActiveConfig('{"seals":[3]}'));
    }

    public function testGetConfiguredSealIdsNormalizesAndDeduplicates(): void
    {
        $ids = SealExemptionService::getConfiguredSealIds((object) ['seals' => ['2', 2, 0, '5']]);
        $this->assertSame([2, 5], $ids);
    }

    public function testResolveSealExemptionConfigReturnsNullWhenInactive(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->createOpportunityWithSealConfig([]);
        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertNull(SealExemptionService::resolveSealExemptionConfig($emc));
    }

    public function testResolveSealExemptionConfigReturnsActiveConfig(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunityWithSealConfig([$seal->id]);
        $emc = $opportunity->evaluationMethodConfiguration;

        $config = SealExemptionService::resolveSealExemptionConfig($emc);

        $this->assertNotNull($config);
        $this->assertSame([$seal->id], $config->seals);
    }

    public function testShouldProcessExemptionOnSendReturnsTrueForSyncedRegistration(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $opportunity = $this->createOpportunityWithSealConfig([1]);
        $registration = $this->createPhaseRegistration($opportunity, $owner);

        $this->assertTrue($this->service->shouldProcessExemptionOnSend($registration));
    }

    public function testShouldProcessExemptionOnSendReturnsTrueOnFirstPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([1]);

        $firstPhase->registerRegistrationMetadata();
        $registration = new Registration();
        $registration->opportunity = $firstPhase;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        $this->assertTrue($this->service->shouldProcessExemptionOnSend($registration));
    }

    public function testShouldProcessExemptionOnSendReturnsFalseOnNonFirstPhaseWithoutSync(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        [, $evalEmc] = $this->createSeparateEvaluationPhaseOpportunity([1]);

        $evalEmc->opportunity->registerRegistrationMetadata();
        $registration = new Registration();
        $registration->opportunity = $evalEmc->opportunity;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        $this->assertFalse($this->service->shouldProcessExemptionOnSend($registration));
    }

    public function testProcessExemptionOnSendSkipsWhenFirstPhaseRegistrationUsesChildPhaseEmc(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createSeparateEvaluationPhaseOpportunity([$seal->id]);

        $firstPhase->registerRegistrationMetadata();
        $registration = new Registration();
        $registration->opportunity = $firstPhase;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        $this->service->processExemptionOnSend($registration);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_DRAFT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
    }

    public function testProcessExemptionOnSendApprovesFirstPhaseWhenAllSealsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$seal->id]);

        $firstPhase->registerRegistrationMetadata();
        $registration = new Registration();
        $registration->opportunity = $firstPhase;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        $this->service->processExemptionOnSend($registration);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
    }

    public function testProcessExemptionOnSendDoesNotApproveFirstPhaseWhenSealsInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->createInvalidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$seal->id]);

        $firstPhase->registerRegistrationMetadata();
        $registration = new Registration();
        $registration->opportunity = $firstPhase;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';
        $registration->save(true);

        $this->service->processExemptionOnSend($registration);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_DRAFT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
    }

    /**
     * @return array{0: Opportunity, 1: \MapasCulturais\Entities\EvaluationMethodConfiguration}
     */
    private function createFirstPhaseDocumentaryOpportunity(array $sealIds = []): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $ownerAgent = $admin->profile;
        $project = new Project();
        $project->name = 'Test Project First Phase Service';
        $project->type = 1;
        $project->owner = $ownerAgent;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($ownerAgent, $project)
            ->fillRequiredProperties()
            ->setProponentTypes(['Pessoa Física'])
            ->save();

        $firstPhase = $builder->getInstance()->firstPhase;

        $emcBuilder = new EvaluationPhaseBuilder($builder);
        $emcBuilder->reset($firstPhase, EvaluationMethods::documentary)
            ->fillRequiredProperties()
            ->save();

        $emc = $emcBuilder->getInstance();

        if ($sealIds) {
            $emc->sealExemptionConfig = (object) ['seals' => $sealIds];
            $emc->save(true);
        }

        return [$firstPhase, $emc];
    }

    /**
     * @return array{0: Opportunity, 1: \MapasCulturais\Entities\EvaluationMethodConfiguration}
     */
    private function createSeparateEvaluationPhaseOpportunity(array $sealIds = []): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $ownerAgent = $admin->profile;
        $project = new Project();
        $project->name = 'Test Project Separate Phases Service';
        $project->type = 1;
        $project->owner = $ownerAgent;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($ownerAgent, $project)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save();

        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->fillRequiredProperties()
            ->save()
            ->done();

        $evalEmc = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        if ($sealIds) {
            $evalEmc->sealExemptionConfig = (object) ['seals' => $sealIds];
            $evalEmc->save(true);
        }

        return [$builder->getInstance()->firstPhase, $evalEmc];
    }
}
