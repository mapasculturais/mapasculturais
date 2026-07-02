<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationCriteriaDeletionTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function getDistributedValuerNames($opportunity, Registration $registration): array
    {
        $valuer_user_ids = array_keys($registration->valuers);
        $valuer_names = [];

        $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão'] ?? [];
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                $valuer_names[] = $relation->agent->name;
            }
        }

        return $valuer_names;
    }

    private function setupTechnicalPhaseWithTwoCriteria(): array
    {
        $owner = $this->userDirector->createUser();
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $app = App::i();
        $app->disableAccessControl();

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->config()
                    ->addSection('sec1', 'Seção 1')
                    ->addCriterion('cri1', 'sec1', 'Critério 1', 0, 10, 1)
                    ->addCriterion('cri2', 'sec1', 'Critério 2', 0, 10, 1)
                    ->done()
                ->save()
                ->addValuers(1, 'Comissão');

        $opportunity = $evaluation_phase_builder->done()->getInstance();
        $config = $opportunity->evaluationMethodConfiguration;

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $config->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $app->enableAccessControl();

        return [$evaluation_phase_builder, $config, $registration, $owner, $admin];
    }

    private function setupQualificationPhaseWithTwoCriteria(): array
    {
        $owner = $this->userDirector->createUser();
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $app = App::i();
        $app->disableAccessControl();

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::qualification)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->config()
                    ->addSection('sec1', 'Seção 1')
                    ->addCriterion('cri1', 'sec1', 'Critério 1')
                    ->addCriterion('cri2', 'sec1', 'Critério 2')
                    ->done()
                ->save()
                ->addValuers(1, 'Comissão');

        $opportunity = $evaluation_phase_builder->done()->getInstance();
        $config = $opportunity->evaluationMethodConfiguration;

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $config->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $app->enableAccessControl();

        return [$evaluation_phase_builder, $config, $registration, $owner, $admin];
    }

    private function removeCriterionFromConfig(EvaluationMethodConfiguration $config, string $criterion_id): void
    {
        $config = $config->refreshed();
        $config->criteria = array_values(array_filter(
            (array) $config->criteria,
            fn($criterion) => $criterion->id !== $criterion_id
        ));
        $config->save(true);
    }

    function testHasStartedEvaluationsDetectsDraftEvaluation(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupTechnicalPhaseWithTwoCriteria();

        $this->assertFalse($config->hasStartedEvaluations());

        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setCriterionScore('cri1', 5.0)
                ->save()
                ->done();

        $this->assertTrue($config->refreshed()->hasStartedEvaluations());
    }

    function testNonAdminCannotDeleteTechnicalCriterionWithStartedEvaluation(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupTechnicalPhaseWithTwoCriteria();
        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setCriterionScore('cri1', 8.0)
                ->setCriterionScore('cri2', 2.0)
                ->save()
                ->done();

        $this->login($owner);

        $this->assertException(PermissionDenied::class, function () use ($config) {
            $this->removeCriterionFromConfig($config, 'cri2');
        });
    }

    function testAdminDeletesTechnicalCriterionAndRemovesFromEvaluations(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupTechnicalPhaseWithTwoCriteria();
        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setCriterionScore('cri1', 8.0)
                ->setCriterionScore('cri2', 2.0)
                ->save()
                ->send()
                ->done();

        $this->login($admin);

        $this->removeCriterionFromConfig($config, 'cri2');

        $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration]);
        $data = (array) $evaluation->evaluationData;

        $this->assertArrayNotHasKey('cri2', $data, 'Critério removido deve sair do evaluation_data');
        $this->assertEquals('8', (string) $evaluation->result, 'Resultado deve considerar apenas critérios restantes');
        $this->assertEquals('8.00', (string) $registration->refreshed()->consolidatedResult);
    }

    function testNonAdminCannotDeleteQualificationCriterionWithStartedEvaluation(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupQualificationPhaseWithTwoCriteria();
        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setQualified('cri1')
                ->setQualified('cri2')
                ->save()
                ->done();

        $this->login($owner);

        $this->assertException(PermissionDenied::class, function () use ($config) {
            $this->removeCriterionFromConfig($config, 'cri2');
        });
    }

    function testAdminDeletesQualificationCriterionAndRemovesFromEvaluations(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupQualificationPhaseWithTwoCriteria();
        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setQualified('cri1')
                ->setQualified('cri2')
                ->save()
                ->send()
                ->done();

        $this->login($admin);

        $this->removeCriterionFromConfig($config, 'cri2');

        $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration]);
        $data = (array) $evaluation->evaluationData;

        $this->assertArrayNotHasKey('cri2', $data, 'Critério removido deve sair do evaluation_data');
        $this->assertEquals('valid', (string) $evaluation->result);
        $this->assertEquals('valid', (string) $registration->refreshed()->consolidatedResult);
    }

    function testAdminDeletesTechnicalSectionAndRemovesCriteriaFromEvaluations(): void
    {
        [$evaluation_phase_builder, $config, $registration, $owner, $admin] = $this->setupTechnicalPhaseWithTwoCriteria();
        $this->login($admin);
        $valuer_name = $this->getDistributedValuerNames($config->opportunity, $registration)[0];

        $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
            ->evaluation($registration)
                ->setCriterionScore('cri1', 6.0)
                ->setCriterionScore('cri2', 4.0)
                ->save()
                ->done();

        $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration]);
        $evaluation->status = RegistrationEvaluation::STATUS_EVALUATED;
        $evaluation->save(true);

        $this->login($admin);

        $config = $config->refreshed();
        $config->criteria = [];
        $config->sections = [];
        $config->save(true);

        $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy([
            'registration' => $registration,
            'status' => RegistrationEvaluation::STATUS_EVALUATED,
        ]);
        $data = (array) $evaluation->evaluationData;

        $this->assertArrayNotHasKey('cri1', $data);
        $this->assertArrayNotHasKey('cri2', $data);
    }
}
