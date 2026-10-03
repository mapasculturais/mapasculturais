<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class ReopenSelectedEvaluationsTest extends TestCase
{
    use OpportunityBuilder, RegistrationDirector, RequestFactory, UserDirector;

    private function createFixture(): array
    {
        $manager = $this->userDirector->createUser('admin');
        $this->login($manager);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $manager->profile, owner_entity: $manager->profile)
            ->fillRequiredProperties()
            ->firstPhase()->setRegistrationPeriod(new Open)->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->addValuers(2, 'Comissão')
                ->done()
            ->getInstance();

        $firstRegistration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $secondRegistration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $firstRegistration = $firstRegistration->refreshed();
        $secondRegistration = $secondRegistration->refreshed();
        $valuerIds = array_keys($firstRegistration->valuers);
        $firstValuer = $this->app->repo('User')->find($valuerIds[0]);
        $secondValuer = $this->app->repo('User')->find($valuerIds[1]);

        $createEvaluation = function ($registration, $valuer): RegistrationEvaluation {
            $evaluation = new RegistrationEvaluation();
            $evaluation->registration = $registration;
            $evaluation->user = $valuer;
            $evaluation->setEvaluationData(['status' => '10']);
            $evaluation->status = RegistrationEvaluation::STATUS_SENT;
            $evaluation->save(true);
            return $evaluation;
        };

        return [
            $manager,
            $opportunity,
            $firstValuer,
            $createEvaluation($firstRegistration, $firstValuer),
            $createEvaluation($secondRegistration, $firstValuer),
            $createEvaluation($firstRegistration, $secondValuer),
        ];
    }

    public function testManagerListsOnlySentEvaluationsFromTheChosenValuer(): void
    {
        [$manager, $opportunity, $valuer, $first, $second, $otherValuer] = $this->createFixture();
        $second->status = RegistrationEvaluation::STATUS_EVALUATED;
        $second->save(true);

        $request = $this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
        ]);
        $this->assertStatus200($request);

        $body = json_decode((string) App::i()->response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(1, $body['total']);
        $this->assertSame([$first->id], array_column($body['evaluations'], 'id'));
        $this->assertSame($first->registration->number, $body['evaluations'][0]['registrationNumber']);
        $this->assertNull($body['nextCursor']);
    }

    public function testListCursorDoesNotSkipAnEvaluationWhenAnEarlierOneWasReopened(): void
    {
        [$manager, $opportunity, $valuer, $first, $second] = $this->createFixture();
        $params = ['opportunityId' => $opportunity->id, 'uid' => $valuer->id, 'limit' => 1];

        $request = $this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: $params);
        $this->assertStatus200($request);
        $page = json_decode((string) App::i()->response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(2, $page['total']);
        $this->assertCount(1, $page['evaluations']);
        $this->assertSame($first->id, $page['nextCursor']);

        $first->status = RegistrationEvaluation::STATUS_EVALUATED;
        $first->save(true);
        $request = $this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: [
            ...$params,
            'afterId' => $page['nextCursor'],
        ]);
        $this->assertStatus200($request);
        $page = json_decode((string) App::i()->response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([$second->id], array_column($page['evaluations'], 'id'));
        $this->assertNull($page['nextCursor']);
    }

    public function testUserWithoutCommitteePermissionCannotListReopenableEvaluations(): void
    {
        [$manager, $opportunity, $valuer] = $this->createFixture();
        $this->login($this->userDirector->createUser());

        $request = $this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
        ]);
        $this->assertStatus403($request);
    }

    public function testListRejectsNonPositivePaginationValues(): void
    {
        [$manager, $opportunity, $valuer] = $this->createFixture();
        $params = ['opportunityId' => $opportunity->id, 'uid' => $valuer->id];

        $this->assertStatus400($this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: [
            ...$params,
            'limit' => -1,
        ]));
        $this->assertStatus400($this->requestFactory->GET('opportunity', 'reopenableEvaluations', query_params: [
            ...$params,
            'afterId' => -1,
        ]));
    }

    public function testManagerReopensOnlySelectedSentEvaluation(): void
    {
        [$manager, $opportunity, $valuer, $selected, $sameValuer, $otherValuer] = $this->createFixture();

        $request = $this->requestFactory->POST('opportunity', 'reopenSelectedEvaluations', payload: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
            'evaluationIds' => [$selected->id],
        ]);
        $this->assertStatus200($request);

        $this->assertSame(RegistrationEvaluation::STATUS_EVALUATED, $selected->refreshed()->status);
        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $sameValuer->refreshed()->status);
        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $otherValuer->refreshed()->status);
    }

    public function testManagerCannotPartiallyReopenASelectionContainingAnotherValuersEvaluation(): void
    {
        [$manager, $opportunity, $valuer, $selected, $sameValuer, $otherValuer] = $this->createFixture();

        $request = $this->requestFactory->POST('opportunity', 'reopenSelectedEvaluations', payload: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
            'evaluationIds' => [$selected->id, $otherValuer->id],
        ]);
        $this->assertStatus400($request);

        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $selected->refreshed()->status);
        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $otherValuer->refreshed()->status);
    }

    public function testManagerReopensSeveralSelectedEvaluations(): void
    {
        [$manager, $opportunity, $valuer, $first, $second, $otherValuer] = $this->createFixture();

        $request = $this->requestFactory->POST('opportunity', 'reopenSelectedEvaluations', payload: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
            'evaluationIds' => [$first->id, $second->id],
        ]);
        $this->assertStatus200($request);

        $this->assertSame(RegistrationEvaluation::STATUS_EVALUATED, $first->refreshed()->status);
        $this->assertSame(RegistrationEvaluation::STATUS_EVALUATED, $second->refreshed()->status);
        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $otherValuer->refreshed()->status);
    }

    public function testManagerCannotReopenASelectionWithAnAlreadyConcludedEvaluation(): void
    {
        [$manager, $opportunity, $valuer, $selected, $concluded] = $this->createFixture();
        $concluded->status = RegistrationEvaluation::STATUS_EVALUATED;
        $concluded->save(true);

        $request = $this->requestFactory->POST('opportunity', 'reopenSelectedEvaluations', payload: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
            'evaluationIds' => [$selected->id, $concluded->id],
        ]);
        $this->assertHttpStatusCode($request, 409);

        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $selected->refreshed()->status);
    }

    public function testUserWithoutCommitteePermissionCannotReopenEvaluation(): void
    {
        [$manager, $opportunity, $valuer, $selected] = $this->createFixture();
        $outsider = $this->userDirector->createUser();
        $this->login($outsider);

        $request = $this->requestFactory->POST('opportunity', 'reopenSelectedEvaluations', payload: [
            'opportunityId' => $opportunity->id,
            'uid' => $valuer->id,
            'evaluationIds' => [$selected->id],
        ]);
        $this->assertStatus403($request);
        $this->assertSame(RegistrationEvaluation::STATUS_SENT, $selected->refreshed()->status);
    }
}
