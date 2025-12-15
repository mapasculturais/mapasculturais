<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use Tests\Traits\Faker;
use Tests\Traits\UserDirector;
use Tests\Traits\RequestFactory;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\RegistrationDirector;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;

class EvaluationRoutesTest extends Abstract\TestCase
{
    use RequestFactory,
        UserDirector,
        Faker,
        OpportunityBuilder,
        RegistrationDirector;

    function testFirstAccessToEvaluationRoute()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuers(2, 'committee 1')
                ->done()
            ->getInstance();
            
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: []
        );

        
        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        
        $registration = $registration->refreshed();

        $valuer_user_id = array_keys($registration->valuers)[0];
        
        foreach($committee as $valuer) {
            if($valuer->user->id == $valuer_user_id) {
                $registration_valuer = $valuer->user->refreshed();
            } 
        }

        $this->login($registration_valuer);

        $evaluation_id = $this->app->conn->fetchScalar("SELECT evaluation_id FROM evaluations WHERE valuer_user_id = :uid AND registration_id = :rid", [
            'uid' => $registration_valuer->id,
            'rid' => $registration->id
        ]);

        $this->assertNull($evaluation_id, 'Garantindo que ANTES de acessar a rota da avaliação o id da avaliação é NULL');

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo status 200 no primeiro acesso à rota "registration.evaluation" para um avaliador da inscrição');

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo status 200 no segundo acesso à rota "registration.evaluation" para um avaliador da inscrição');

        $evaluation_id = $this->app->conn->fetchScalar("SELECT evaluation_id FROM evaluations WHERE valuer_user_id = :uid AND registration_id = :rid", [
            'uid' => $registration_valuer->id,
            'rid' => $registration->id
        ]);

        $this->assertNull($evaluation_id, 'Garantindo que DEPOIS de acessar a rota da avaliação o id da avaliação é NULL');
    }

    function testEvaluationCreationOnSaveRoute()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuers(2, 'committee 1')
                ->done()
            ->getInstance();
            
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: []
        );

        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $registration = $registration->refreshed();

        $valuer_user_id = array_keys($registration->valuers)[0];

        foreach ($committee as $valuer) {
            if ($valuer->user->id == $valuer_user_id) {
                $registration_valuer = $valuer->user->refreshed();
            }
        }

        $this->login($registration_valuer);

        $evaluation_id = $this->app->conn->fetchScalar("SELECT evaluation_id FROM evaluations WHERE valuer_user_id = :uid AND registration_id = :rid", [
            'uid' => $registration_valuer->id,
            'rid' => $registration->id
        ]);

        $request = $this->requestFactory->POST(
            controller_id: 'registration',
            action: 'saveEvaluation',
            url_params: [$registration->id],
            payload: [
                'data' => [
                    'status' => Registration::STATUS_WAITLIST,
                    'obs' => $this->faker->text()
                ]
            ]
        );

        $this->assertStatus200($request, 'Garantindo status 200 na rota "registration.saveEvaluation" para criação e salvamento dos dados da avaliação');

        $evaluation_id = $this->app->conn->fetchScalar("SELECT evaluation_id FROM evaluations WHERE valuer_user_id = :uid AND registration_id = :rid", [
            'uid' => $registration_valuer->id,
            'rid' => $registration->id
        ]);

        $this->assertNotNull($evaluation_id, 'Garantindo que DEPOIS de salvar a avaliação pela rota /registration/saveEvaluation a avaliação foi criada e o id da avaliação NÃO é mais NULL');
    }

    function testValuerAccessToAnotherValuerRegistration()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 1)
                ->save()
                ->addValuers(2, 'committee 1')
                ->done()
            ->getInstance();
            
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: []
        );

        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        
        $registration = $registration->refreshed();

        $valuer_user_id = array_keys($registration->valuers)[0];
        
        foreach($committee as $valuer) {
            if($valuer->user->id != $valuer_user_id) {
                $another_valuer = $valuer->user->refreshed();
            }
        }

        $this->login($another_valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus403($request, 'Garantindo status 403 no primeiro acesso à rota "registration.evaluation" para um avaliador que não é avaliador da inscrição');
    }

    public function testReplaceValuerRoute()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        // Criar oportunidade com 2 avaliadores e diferentes tipos de avaliações
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->createSentRegistrations(number_of_registrations: 8)
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('committee 1', 2)
                ->save()

                ->addValuer('committee 1', name: 'fulano')->done()
                ->addValuer('committee 1', name: 'ciclano')->done()

                ->redistributeCommitteeRegistrations()

                ->withValuer('committee 1', 'fulano')
                    ->createDraftEvaluation()
                    ->createDraftEvaluation()
                    ->createSentEvaluation()
                    ->createSentEvaluation()
                    ->createConcludedEvaluation()
                    ->done()
                ->withValuer('committee 1', 'ciclano')
                    ->createDraftEvaluation()
                    ->createDraftEvaluation()
                    ->createSentEvaluation()
                    ->createSentEvaluation()
                    ->done()
                ->done()
            ->getInstance();

        $emc = $opportunity->evaluationMethodConfiguration->refreshed();
        $old_evaluator_relation = $emc->agentRelations[0];
        $new_evaluator = $this->userDirector->createUser();

        $response = $this->requestFactory->POST("evaluationMethodConfiguration", "replaceValuer", [$opportunity->evaluationMethodConfiguration->id], [
            'relation' => $old_evaluator_relation->id,
            'newValuerAgentId' => $new_evaluator->profile->id,
        ]);

        $this->assertStatus200($response, 'Garantindo status 200 no acesso à rota "evaluationMethodConfiguration.replaceValuer" para substituir um avaliador');

        $agent = $this->userDirector->createUser();
        $this->login($agent);

        $response = $this->requestFactory->POST("evaluationMethodConfiguration", "replaceValuer", [$opportunity->evaluationMethodConfiguration->id], [
            'relation' => $old_evaluator_relation->id,
            'newValuerAgentId' => $new_evaluator->profile->id,
        ]);

        $this->assertStatus403($response, 'Garantindo status 403 no acesso à rota "evaluationMethodConfiguration.replaceValuer" para um usuário que não é administrador');
    }
}
