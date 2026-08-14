<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationMethodTechnicalTiebreakerTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function createTechnicalOpportunityWithTiebreaker(array $tiebreaker_config): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->done();

        $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(0)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->config()
                    ->addSection('sec-1', 'Seção 1')
                    ->addCriterion('c-1', 'sec-1', 'Critério 1', 0, 10, 1)
                    ->addCriterion('c-2', 'sec-1', 'Critério 2', 0, 10, 1)
                    ->setTiebreakerCriteriaConfiguration($tiebreaker_config)
                    ->done()
                ->save()
                ->addValuers(2, 'Comissão')
                ->done()
            ->save();

        return $this->opportunityBuilder->getInstance()->refreshed();
    }

    private function evaluateRegistrationWithTwoValuers(
        Opportunity $opportunity,
        Registration $registration,
        float $valuer1_criterion1_score,
        float $valuer1_criterion2_score,
        float $valuer2_criterion1_score,
        float $valuer2_criterion2_score
    ): void {
        $app = App::i();
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();
        $valuer_user_ids = array_keys($registration->valuers);

        $scores = [
            [$valuer1_criterion1_score, $valuer1_criterion2_score],
            [$valuer2_criterion1_score, $valuer2_criterion2_score],
        ];

        foreach ($valuer_user_ids as $i => $user_id) {
            $user = $app->repo('User')->find($user_id);
            $evaluation = new RegistrationEvaluation();
            $evaluation->registration = $registration;
            $evaluation->user = $user;
            $evaluation->setEvaluationData((object) [
                'c-1' => $scores[$i][0],
                'c-2' => $scores[$i][1],
                'obs' => 'teste',
            ]);
            $evaluation->save();
            $app->disableAccessControl();
            $evaluation->send();
            $app->enableAccessControl();
        }
    }

    private function setSentTimestamp(Registration $registration, string $timestamp): void
    {
        $app = App::i();
        $app->em->getConnection()->update(
            'registration',
            ['sent_timestamp' => $timestamp],
            ['id' => $registration->id]
        );
    }

    private function getOrderedRegistrationIds(Opportunity $opportunity): array
    {
        $app = App::i();
        /** @var OpportunityController $opportunity_controller */
        $opportunity_controller = $app->controller('opportunity');

        $result = $opportunity_controller->apiFindRegistrations($opportunity, [
            '@select' => 'id,score',
            '@order' => '@quota',
            '__enableQuota' => true,
        ], true);

        return array_map(fn($registration) => (int) $registration['id'], $result->registrations);
    }

    public function testTiebreakerByCriterionOrdersHigherAverageFirst(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithTiebreaker([
            (object) [
                'id' => 1,
                'name' => 'critério 1',
                'criterionType' => 'criterion',
                'preferences' => 'c-1',
            ],
        ]);

        $registration_a = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_b = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_c = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        // A e B empatam em score final (10), mas B vence em c-1.
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_a, 2, 8, 2, 8);
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_b, 8, 2, 8, 2);
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_c, 6, 2, 6, 2);

        $ids = $this->getOrderedRegistrationIds($opportunity);

        $this->assertEquals(
            $registration_b->id,
            $ids[0],
            'Certificando que, com desempate por critério c-1, a inscrição com maior média no critério vem primeiro'
        );
    }

    public function testTiebreakerBySubmissionDateSmallestPrefersOldest(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithTiebreaker([
            (object) [
                'id' => 1,
                'name' => 'critério 1',
                'criterionType' => 'submissionDate',
                'preferences' => 'smallest',
            ],
        ]);

        $registration_oldest = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_newest = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $this->setSentTimestamp($registration_oldest, '2024-01-10 10:00:00');
        $this->setSentTimestamp($registration_newest, '2024-01-20 10:00:00');

        // Mantém score empatado para forçar uso do critério de data.
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_oldest, 7, 3, 7, 3);
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_newest, 7, 3, 7, 3);

        $ids = $this->getOrderedRegistrationIds($opportunity);

        $this->assertEquals(
            $registration_oldest->id,
            $ids[0],
            'Certificando que, com preferência ao menor valor de data, a inscrição mais antiga vem primeiro'
        );
    }

    public function testTiebreakerUsesSecondRuleWhenFirstStillTies(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithTiebreaker([
            (object) [
                'id' => 1,
                'name' => 'critério 1',
                'criterionType' => 'criterion',
                'preferences' => 'c-1',
            ],
            (object) [
                'id' => 2,
                'name' => 'critério 2',
                'criterionType' => 'submissionDate',
                'preferences' => 'largest',
            ],
        ]);

        $registration_oldest = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_newest = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $this->setSentTimestamp($registration_oldest, '2024-01-10 10:00:00');
        $this->setSentTimestamp($registration_newest, '2024-01-20 10:00:00');

        // Empata score e c-1 para obrigar desempate na segunda regra (data).
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_oldest, 5, 5, 5, 5);
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_newest, 5, 5, 5, 5);

        $ids = $this->getOrderedRegistrationIds($opportunity);

        $this->assertEquals(
            $registration_newest->id,
            $ids[0],
            'Certificando que, quando a primeira regra empata, a segunda regra define a ordem'
        );
    }
}
