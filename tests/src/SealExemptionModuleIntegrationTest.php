<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Integration tests for SealExemption\Module hooks and guards.
 */
class SealExemptionModuleIntegrationTest extends TestCase
{
    use AgentDirector;
    use OpportunityBuilder;
    use SealDirector;
    use UserDirector;

    /**
     * Helper: create a project-based opportunity with a data collection phase
     * and an evaluation phase of the given method.
     */
    private function createOpportunity(EvaluationMethods $evaluationMethod, array $sealIds = []): Opportunity
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

        $emc = $builder->addEvaluationPhase($evaluationMethod)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $opportunity = $emc->opportunity;

        if ($sealIds) {
            $emc->sealExemptionConfig = (object) [
                'seals' => $sealIds,
                'label' => 'Test Label',
            ];
            $emc->save(true);
        }

        return $opportunity;
    }

    /**
     * Helper: create a draft registration in the given opportunity.
     */
    private function createDraftRegistration(Opportunity $opportunity, Agent $owner, ?int $previousPhaseId = null): Registration
    {
        $opportunity->registerRegistrationMetadata();

        $registration = new Registration();
        $registration->opportunity = $opportunity;
        $registration->owner = $owner;
        $registration->proponentType = 'Pessoa Física';
        $registration->range = 'Test Range';

        if ($previousPhaseId !== null) {
            $registration->previousPhaseRegistrationId = $previousPhaseId;
        }

        $registration->save(true);

        return $registration;
    }

    /**
     * Helper: create a fully valid seal relation for an agent.
     */
    private function createFullyValidSealForAgent(Agent $agent, Agent $applyingAgent): int
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

        return $seal->id;
    }

    public function testSendAfterHookGrantsExemptionWhenAllSealsValid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$sealId]);
        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);

        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
    }

    public function testSendAfterHookDoesNothingOnFirstPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$sealId]);

        // first phase = data collection phase (the base opportunity itself)
        $firstPhase = $opportunity->firstPhase;
        $registration = $this->createDraftRegistration($firstPhase, $owner);
        // no previousPhaseRegistrationId, as in a real first-phase submission

        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
    }

    public function testSendAfterHookDoesNothingWhenPhaseHasNoConfig(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        $opportunity = $this->createOpportunity(EvaluationMethods::simple);
        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);

        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
    }

    public function testSendAfterHookDoesNothingForTechnicalEvaluation(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::technical);
        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);

        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
    }

    public function testEmcSaveBeforeBlocksEditAfterPhaseOpensWithRegistrations(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);

        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->evaluationFrom = (new DateTime())->sub(new \DateInterval('P1D'));
        $emc->evaluationTo = (new DateTime())->add(new \DateInterval('P7D'));
        $emc->save(true);

        // Create a registration in the phase so hasActiveRegistrations() returns true.
        $this->createDraftRegistration($opportunity, $owner, 999999);

        $this->expectException(PermissionDenied::class);

        $emc->sealExemptionConfig = (object) [
            'seals' => [$seal->id, 99999],
            'label' => 'Changed Label',
        ];
        $emc->save(true);
    }

    public function testEmcSaveBeforeAllowsEditWhenPhaseHasNoRegistrations(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->evaluationFrom = (new DateTime())->sub(new \DateInterval('P1D'));
        $emc->evaluationTo = (new DateTime())->add(new \DateInterval('P7D'));
        $emc->save(true);

        // No registrations in the phase — editing is still allowed.
        $emc->sealExemptionConfig = (object) [
            'seals' => [$seal->id],
            'label' => 'Changed Label',
        ];
        $emc->save(true);

        $this->assertSame('Changed Label', $emc->refreshed()->sealExemptionConfig->label);
    }
}
