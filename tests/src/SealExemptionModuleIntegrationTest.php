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
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
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
                'object_type' => 'agentsealrelation',
                'agent_id'    => $agent->id,
                'seal_id'     => $seal->id,
            ]
        );

        return $seal->id;
    }

    /**
     * Helper: create invalid seal relation for an agent.
     */
    private function createInvalidSealForAgent(Agent $agent, Agent $applyingAgent): int
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
                'object_type' => 'agentsealrelation',
                'agent_id'    => $agent->id,
                'seal_id'     => $seal->id,
            ]
        );

        return $seal->id;
    }

    /**
     * Helper: oportunidade com coleta + avaliação documental na mesma fase (1ª fase).
     *
     * @return array{0: Opportunity, 1: \MapasCulturais\Entities\EvaluationMethodConfiguration}
     */
    private function createFirstPhaseDocumentaryOpportunity(array $sealIds = []): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $ownerAgent = $admin->profile;
        $project = new Project();
        $project->name = 'Test Project First Phase Eval';
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
     * Helper: edital com coleta (1ª fase) e avaliação em oportunidade-filha separada.
     *
     * @return array{0: Opportunity, 1: \MapasCulturais\Entities\EvaluationMethodConfiguration}
     */
    private function createSeparateEvaluationPhaseOpportunity(array $sealIds = []): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $project = new Project();
        $project->name = 'Test Project Separate Phases';
        $project->type = 1;
        $project->owner = $owner;
        $project->save(true);

        $builder = $this->opportunityBuilder
            ->reset($owner, $project)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save();

        // 1ª avaliação fica na oportunidade de coleta (sem config de selos).
        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->fillRequiredProperties()
            ->save()
            ->done();

        // 2ª avaliação em oportunidade-filha separada (onde ficam os selos).
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

    /**
     * Helper: abre a fase de avaliação (evaluationFrom no passado).
     */
    private function openEvaluationPhase(\MapasCulturais\Entities\EvaluationMethodConfiguration $emc): void
    {
        $emc->evaluationFrom = (new DateTime())->sub(new \DateInterval('P1D'));
        $emc->evaluationTo = (new DateTime())->add(new \DateInterval('P7D'));
        $emc->save(true);
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

    public function testSendAfterHookDoesNothingOnFirstPhaseWithoutLocalEvaluationConfig(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createSeparateEvaluationPhaseOpportunity([$sealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
    }

    public function testSendAfterHookGrantsExemptionWhenSyncedToSeparateEvaluationPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase, $evalEmc] = $this->createSeparateEvaluationPhaseOpportunity([$sealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);

        $evalRegistration = $this->createDraftRegistration(
            $evalEmc->opportunity,
            $owner,
            $registration->id
        );
        $evalRegistration->send(false);

        $evalRegistration = $evalRegistration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $evalRegistration->status);
        $this->assertSame('granted', $evalRegistration->sealExemptionStatus);
    }

    public function testSendAfterHookGrantsExemptionOnFirstPhaseWithActiveConfig(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$sealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
        $this->assertNotNull($registration->sealExemptionTimestamp);
    }

    public function testSendAfterHookDoesNotApproveFirstPhaseWhenSealsInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $invalidSealId = $this->createInvalidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$invalidSealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
    }

    /**
     * Isencao AUTOMATICA no envio (send:after) — permanece all-or-nothing.
     *
     * Se 1 de N selos tem computed_status='invalid', a isencao automatica NAO
     * e concedida (a inscricao fica em STATUS_SENT para avaliacao normal).
     *
     * IMPORTANTE: este teste cobre a ISENCAO AUTOMATICA (hook send:after →
     * applyExemptionCheck), que permanece all-or-nothing por restricao da
     * especificacao (spec-b9e4a024.md §3.9). NAO confundir com a CONCESSAO
     * PARCIAL apos avaliacao manual (status 3/8), que e testada em
     * SealPartialGrantTest.
     */
    public function testSendAfterHookDoesNotApproveFirstPhaseWhenOneOfMultipleSealsInvalid(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $validSealId = $this->createFullyValidSealForAgent($owner, $admin->profile);
        $invalidSealId = $this->createInvalidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$validSealId, $invalidSealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
    }

    public function testSendAfterHookMarksAgentMissingOnFirstPhaseWhenProponentUnresolved(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$sealId]);
        $firstPhase->useAgentRelationColetivo = 'required';
        $firstPhase->save(true);

        $registration = $this->createDraftRegistration($firstPhase, $owner);
        $registration->proponentType = 'Pessoa Jurídica';
        $registration->save(true);

        $registration->send(false);

        $registration = $registration->refreshed();
        $this->assertSame('agent_missing', $registration->sealExemptionStatus);
        $this->assertSame(Registration::STATUS_SENT, $registration->status);
    }

    public function testSendAfterHookGrantsExemptionAgainAfterReturnToDraft(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $sealId = $this->createFullyValidSealForAgent($owner, $admin->profile);

        [$firstPhase] = $this->createFirstPhaseDocumentaryOpportunity([$sealId]);

        $registration = $this->createDraftRegistration($firstPhase, $owner);

        $registration->send(false);
        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);

        $registration->setStatusToDraft(false);
        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_DRAFT, $registration->status);
        $this->assertNull($registration->sealExemptionStatus);
        $this->assertNull($registration->sealExemptionTimestamp);

        $registration->send(false);
        $registration = $registration->refreshed();
        $this->assertSame(Registration::STATUS_APPROVED, $registration->status);
        $this->assertSame('granted', $registration->sealExemptionStatus);
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

        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);
        $registration->send(false);

        $this->expectException(PermissionDenied::class);

        $emc->sealExemptionConfig = (object) [
            'seals' => [$seal->id, 99999],
        ];
        $emc->save(true);
    }

    public function testEmcSaveBeforeBlocksDisablingWhenSentRegistrationsExist(): void
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

        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);
        $registration->send(false);

        $this->expectException(PermissionDenied::class);

        $emc->sealExemptionConfig = (object) ['seals' => []];
        $emc->save(true);
    }

    public function testEmcSaveBeforeAllowsEditWhenOnlyDraftRegistrationsExist(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $owner = $this->agentDirector->createAgent($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->evaluationFrom = (new DateTime())->sub(new \DateInterval('P1D'));
        $emc->evaluationTo = (new DateTime())->add(new \DateInterval('P7D'));
        $emc->save(true);

        $this->createDraftRegistration($opportunity, $owner, 999999);

        $emc->sealExemptionConfig = (object) ['seals' => []];
        $emc->save(true);

        $this->assertSame([], $emc->refreshed()->sealExemptionConfig->seals);
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
        ];
        $emc->save(true);

        $this->assertSame([$seal->id], $emc->refreshed()->sealExemptionConfig->seals);
    }

    public function testHasActiveRegistrationsIgnoresDraftRegistrations(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $this->createDraftRegistration($opportunity, $owner, 999999);

        $this->assertFalse($emc->hasActiveRegistrations());
    }

    public function testHasActiveRegistrationsCountsSentRegistrations(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);
        $registration->send(false);

        $this->assertTrue($emc->refreshed()->hasActiveRegistrations());
    }

    public function testGetCanEditSealConfigAllowsEditWhenOnlyDraftRegistrationsExist(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $this->createDraftRegistration($opportunity, $owner, 999999);

        $this->assertTrue($emc->getCanEditSealConfig());
    }

    public function testGetCanEditSealConfigBlocksEditWhenSentRegistrationsExist(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);
        $registration->send(false);

        $this->assertFalse($emc->refreshed()->getCanEditSealConfig());
    }

    public function testGetCanEditSealConfigAllowsEditAgainAfterRegistrationReturnsToDraft(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $registration = $this->createDraftRegistration($opportunity, $owner, 999999);
        $registration->send(false);
        $this->assertFalse($emc->refreshed()->getCanEditSealConfig());

        $registration->refreshed()->setStatusToDraft(false);

        $this->assertTrue($emc->refreshed()->getCanEditSealConfig());
        $this->assertFalse($emc->refreshed()->hasActiveRegistrations());
    }

    public function testGetCanEditSealConfigReturnsFalseForTechnicalEvaluation(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::technical);

        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertFalse($emc->getCanEditSealConfig());
    }

    public function testSimplifyExposesCanEditSealConfigForController(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $opportunity = $this->createOpportunity(EvaluationMethods::simple, [$seal->id]);

        $emc = $opportunity->evaluationMethodConfiguration;
        $this->openEvaluationPhase($emc);

        $simplified = $emc->simplify('id,sealExemptionConfig,canEditSealConfig');

        $this->assertTrue($simplified->canEditSealConfig);
        $this->assertSame([$seal->id], $simplified->sealExemptionConfig->seals);
    }

    public function testFirstPhaseExemptionConfigEditableWithDraftRegistrationAfterPhaseOpens(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createAgent($admin->profile);
        $seal = $this->sealDirector->createSeal($admin->profile);
        $otherSeal = $this->sealDirector->createSeal($admin->profile);

        [$firstPhase, $emc] = $this->createFirstPhaseDocumentaryOpportunity([$seal->id]);
        $this->openEvaluationPhase($emc);

        $this->createDraftRegistration($firstPhase, $owner);

        $this->assertTrue($emc->getCanEditSealConfig());

        $emc->sealExemptionConfig = (object) ['seals' => [$seal->id, $otherSeal->id]];
        $emc->save(true);

        $this->assertSame(
            [$seal->id, $otherSeal->id],
            $emc->refreshed()->sealExemptionConfig->seals
        );
    }
}
