<?php

namespace Tests;

use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use OpportunityAppealPhase\Entities\AppealTechnicalCorrection;
use OpportunityAppealPhase\Services\AppealTechnicalCorrectionConflict;
use OpportunityAppealPhase\Services\AppealTechnicalCorrectionService;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class AppealTechnicalCorrectionIntegrationTest extends Abstract\TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    public function testDeferredAppealReplacesSelectedScoreAndKeepsRegistrationStatus(): void
    {
        $scenario = $this->createScenario();
        $source = $scenario['source'];
        $appeal = $scenario['appeal'];
        $relator = $scenario['relator'];
        $firstEvaluation = $scenario['firstEvaluation'];
        $statusBefore = $source->status;

        $service = new AppealTechnicalCorrectionService();
        $draft = $service->saveDraft($appeal, $relator, [
            'expectedVersion' => 0,
            'reason' => 'O recurso reconheceu erro no critério 1.',
            'evaluations' => [[
                'evaluationId' => $firstEvaluation->id,
                'criteria' => ['criterion-1' => 8.0],
            ]],
        ]);

        $context = $service->getContext($appeal, $relator);
        $evaluationVersions = [];
        foreach ($context['evaluations'] as $evaluation) {
            $evaluationVersions[$evaluation['id']] = $evaluation['version'];
        }
        $correction = $service->resolve($appeal, $relator, [
            'expectedVersion' => $draft['version'],
            'evaluationVersions' => $evaluationVersions,
            'reason' => 'O recurso reconheceu erro no critério 1.',
            'confirmNoScoreChange' => false,
        ]);

        $source = $source->refreshed();
        $firstEvaluation = $firstEvaluation->refreshed();
        $this->assertSame(AppealTechnicalCorrection::STATUS_APPLIED, $correction->status);
        $this->assertSame(8.0, (float) $firstEvaluation->evaluationData->{'criterion-1'});
        $this->assertSame(7.0, (float) $source->consolidatedResult);
        $this->assertSame(7.0, (float) $source->score);
        $this->assertSame($statusBefore, $source->status);
        $this->assertCount(1, $correction->items);
        $this->assertSame(4.0, (float) $correction->items->first()->changedCriteria['criterion-1']['before']);
        $revisionCount = (int) $this->app->conn->fetchOne(
            'SELECT COUNT(*) FROM entity_revision WHERE object_type = :type AND object_id = :id',
            ['type' => RegistrationEvaluation::class, 'id' => $firstEvaluation->id]
        );
        $this->assertGreaterThanOrEqual(2, $revisionCount);
    }

    public function testDeferredAppealCanBeConfirmedWithoutChangingScores(): void
    {
        $scenario = $this->createScenario();
        $source = $scenario['source'];
        $before = [
            'consolidatedResult' => (float) $source->consolidatedResult,
            'score' => (float) $source->score,
            'status' => $source->status,
        ];

        $correction = (new AppealTechnicalCorrectionService())->resolve(
            $scenario['appeal'],
            $scenario['relator'],
            [
                'expectedVersion' => 0,
                'reason' => 'O recurso é procedente, mas não produz alteração nos critérios técnicos.',
                'confirmNoScoreChange' => true,
            ]
        );

        $source = $source->refreshed();
        $this->assertSame(AppealTechnicalCorrection::STATUS_CONFIRMED_NO_CHANGE, $correction->status);
        $this->assertTrue((bool) $correction->confirmNoScoreChange);
        $this->assertCount(0, $correction->items);
        $snapshot = (array) $correction->criteriaConfigurationSnapshot;
        $this->assertCount(1, (array) ($snapshot['criteria'] ?? []));
        $this->assertSame($before['consolidatedResult'], (float) $source->consolidatedResult);
        $this->assertSame($before['score'], (float) $source->score);
        $this->assertSame($before['status'], $source->status);
    }

    public function testCorrectionRejectsMissingEvaluationRevisionWithoutChangingScore(): void
    {
        $scenario = $this->createScenario();
        $service = new AppealTechnicalCorrectionService();
        $draft = $service->saveDraft($scenario['appeal'], $scenario['relator'], [
            'expectedVersion' => 0,
            'reason' => 'Correção sujeita a controle de concorrência.',
            'evaluations' => [[
                'evaluationId' => $scenario['firstEvaluation']->id,
                'criteria' => ['criterion-1' => 8.0],
            ]],
        ]);

        try {
            $service->resolve($scenario['appeal'], $scenario['relator'], [
                'expectedVersion' => $draft['version'],
                'evaluationVersions' => [],
                'reason' => 'Correção sujeita a controle de concorrência.',
                'confirmNoScoreChange' => false,
            ]);
            $this->fail('A correção deveria exigir a versão da avaliação técnica.');
        } catch (AppealTechnicalCorrectionConflict $error) {
            $this->assertStringContainsString('versão da avaliação técnica', $error->getMessage());
        }

        $evaluation = $scenario['firstEvaluation']->refreshed();
        $this->assertSame(4.0, (float) $evaluation->evaluationData->{'criterion-1'});
    }

    private function createScenario(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter())
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->config()
                    ->addSection('section-1', 'Mérito')
                    ->addCriterion('criterion-1', 'section-1', 'Critério 1', min: 0, max: 10, weight: 1)
                    ->done()
                ->save()
                ->addValuers(2, 'Comissão');

        $technicalOpportunity = $this->opportunityBuilder->save()->refresh()->getInstance();
        $source = $this->registrationDirector->createSentRegistration($technicalOpportunity, data: []);
        $technicalRelations = $technicalOpportunity->evaluationMethodConfiguration->getAgentRelations();
        $this->app->conn->executeStatement(
            'UPDATE registration SET valuers = :valuers WHERE id = :id',
            [
                'valuers' => json_encode([
                    (string) $technicalRelations[0]->agent->user->id => 'Comissão',
                    (string) $technicalRelations[1]->agent->user->id => 'Comissão',
                ]),
                'id' => $source->id,
            ]
        );
        $source = $source->refreshed();
        $firstEvaluation = $this->createSentEvaluation($source, $technicalRelations[0]->agent->user, 4.0);
        $this->createSentEvaluation($source, $technicalRelations[1]->agent->user, 6.0);
        $source = $source->refreshed();
        $source->consolidateResult();
        $source = $source->refreshed();

        $appealOpportunity = $this->createAppealOpportunity($technicalOpportunity);
        $firstAppealValuer = $this->userDirector->createUser();
        $relator = $this->userDirector->createUser();
        $appealOpportunity->evaluationMethodConfiguration
            ->createAgentRelation($firstAppealValuer->profile, 'Recurso', true)
            ->save(true);
        $appealOpportunity->evaluationMethodConfiguration
            ->createAgentRelation($relator->profile, 'Recurso', true)
            ->save(true);

        $appeal = $this->createAppealRegistration($appealOpportunity, $source);
        $this->app->conn->executeStatement(
            'UPDATE registration SET valuers = :valuers WHERE id = :id',
            [
                'valuers' => json_encode([
                    (string) $firstAppealValuer->id => 'Recurso',
                    (string) $relator->id => 'Recurso',
                ]),
                'id' => $appeal->id,
            ]
        );
        $appeal = $appeal->refreshed();
        $appeal->appealTechnicalCorrectionRelatorUserId = $relator->id;
        $this->app->disableAccessControl();
        $appeal->save(true);
        $this->app->enableAccessControl();
        $this->createSentAppealEvaluation($appeal, $firstAppealValuer);

        return compact('source', 'appeal', 'relator', 'firstEvaluation');
    }

    private function createSentEvaluation(Registration $registration, $user, float $score): RegistrationEvaluation
    {
        $evaluation = new RegistrationEvaluation();
        $evaluation->registration = $registration;
        $evaluation->user = $user;
        $evaluation->setEvaluationData((object) ['criterion-1' => $score, 'obs' => 'Parecer técnico']);
        $evaluation->status = RegistrationEvaluation::STATUS_SENT;
        $evaluation->sentTimestamp = new \DateTime();
        $this->app->disableAccessControl();
        $evaluation->save(true);
        $this->app->enableAccessControl();
        return $evaluation;
    }

    private function createAppealOpportunity(Opportunity $sourceOpportunity): Opportunity
    {
        $className = $sourceOpportunity->getSpecializedClassName();
        $appeal = new $className();
        $appeal->parent = $sourceOpportunity;
        $appeal->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal->name = 'Recurso da avaliação técnica';
        $appeal->ownerEntity = $sourceOpportunity->ownerEntity;
        $appeal->isDataCollection = true;
        $appeal->isAppealPhase = true;
        $appeal->save(true);

        $sourceOpportunity->appealPhase = $appeal;
        $sourceOpportunity->save(true);

        $configuration = new EvaluationMethodConfiguration();
        $configuration->opportunity = $appeal;
        $configuration->type = 'continuous';
        $configuration->publishEvaluationDetails = true;
        $configuration->save(true);
        $appeal->evaluationMethodConfiguration = $configuration;
        return $appeal;
    }

    private function createAppealRegistration(Opportunity $appealOpportunity, Registration $source): Registration
    {
        $appeal = new Registration();
        $appeal->opportunity = $appealOpportunity;
        $appeal->owner = $source->owner;
        $appeal->number = $source->number;
        $appeal->status = Registration::STATUS_SENT;
        $this->app->disableAccessControl();
        $appeal->save(true);
        $this->app->enableAccessControl();
        return $appeal;
    }

    private function createSentAppealEvaluation(Registration $appeal, $valuer): void
    {
        $evaluation = new RegistrationEvaluation();
        $evaluation->registration = $appeal;
        $evaluation->user = $valuer;
        $evaluation->setEvaluationData((object) [
            'status' => Registration::STATUS_APPROVED,
            'obs' => 'Recurso deferido.',
        ]);
        $evaluation->status = RegistrationEvaluation::STATUS_SENT;
        $evaluation->sentTimestamp = new \DateTime();
        $this->app->disableAccessControl();
        $evaluation->save(true);
        $this->app->enableAccessControl();
    }
}
