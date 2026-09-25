<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\Faker;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Garante que saveEvaluation não cria/salva avaliação fora de valuers nem autoavaliação,
 * preservando a edição legada do gestor sobre avaliação já existente de outro avaliador.
 */
class EvaluationSavePermissionTest extends TestCase
{
    use RequestFactory;
    use UserDirector;
    use Faker;
    use OpportunityBuilder;
    use RegistrationDirector;
    use RegistrationBuilder;

    private function createOpportunityWithCommittee(int $valuers = 2, int $valuers_per_registration = 1)
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
                ->setCommitteeValuersPerRegistration('committee 1', $valuers_per_registration)
                ->save()
                ->addValuers($valuers, 'committee 1')
                ->done()
            ->getInstance();

        return [$admin, $opportunity];
    }

    private function postSaveEvaluation(Registration $registration, array $extra_payload = [], ?string $status = null)
    {
        $url_params = [$registration->id];
        if ($status !== null) {
            $url_params = [
                'id' => $registration->id,
                'status' => $status,
            ];
        }

        return $this->requestFactory->POST(
            controller_id: 'registration',
            action: 'saveEvaluation',
            url_params: $url_params,
            payload: array_merge([
                'data' => [
                    'status' => Registration::STATUS_WAITLIST,
                    'obs' => $this->faker->text(),
                ],
            ], $extra_payload)
        );
    }

    public function testAssignedValuerCanSaveEvaluation(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer_user_id = array_keys($registration->valuers)[0];
        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];
        foreach ($committee as $valuer) {
            if ($valuer->user->id == $valuer_user_id) {
                $registration_valuer = $valuer->user->refreshed();
            }
        }

        $this->login($registration_valuer);

        $request = $this->postSaveEvaluation($registration);
        $this->assertStatus200($request, 'Avaliador atribuído em valuers deve conseguir salvar a avaliação');
    }

    public function testOpportunityOwnerOutsideValuersCannotSaveEvaluation(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $this->assertFalse(
            isset($registration->valuers[$admin->id]),
            'Pré-condição: dono do edital não deve estar em valuers'
        );

        $this->login($admin);

        $request = $this->postSaveEvaluation($registration);
        $this->assertStatus403($request, 'Dono do edital fora de valuers não deve salvar avaliação');
    }

    public function testProponentOnCommitteeCannotSaveOwnRegistrationEvaluation(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee(valuers: 1, valuers_per_registration: 2);
        $this->login($admin);

        $proponent = $this->userDirector->createUser();
        $registration = $this->registrationBuilder
            ->reset($opportunity, $proponent->profile)
            ->fillRequiredProperties()
            ->save()
            ->send()
            ->getInstance();

        // Coloca o proponente na comissão (como no caso Vinícius)
        $opportunity->evaluationMethodConfiguration->createAgentRelation(
            agent: $proponent->profile,
            group: 'committee 1',
            has_control: true,
            save: true,
            flush: true
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $this->assertFalse(
            isset($registration->valuers[$proponent->id]),
            'Pré-condição: proponente não deve entrar em valuers da própria inscrição'
        );

        $this->login($proponent);

        $request = $this->postSaveEvaluation($registration);
        $this->assertStatus403($request, 'Proponente na comissão não deve avaliar a própria inscrição');
    }

    public function testOpportunityOwnerCanEditExistingEvaluationOfAnotherValuer(): void
    {
        [$admin, $opportunity] = $this->createOpportunityWithCommittee();

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuer_user_id = array_keys($registration->valuers)[0];
        $committee = $opportunity->evaluationMethodConfiguration->relatedAgents['committee 1'];
        foreach ($committee as $valuer) {
            if ($valuer->user->id == $valuer_user_id) {
                $registration_valuer = $valuer->user->refreshed();
            }
        }

        // Cria avaliação existente do avaliador (bypass de ACL do builder de testes)
        $app = App::i();
        $app->disableAccessControl();
        $evaluation = new RegistrationEvaluation();
        $evaluation->registration = $registration;
        $evaluation->user = $registration_valuer;
        $evaluation->status = RegistrationEvaluation::STATUS_DRAFT;
        $evaluation->evaluationData = (object) [
            'status' => Registration::STATUS_WAITLIST,
            'obs' => 'rascunho',
        ];
        $evaluation->save(true);
        $app->enableAccessControl();

        $this->login($admin);

        $request = $this->postSaveEvaluation($registration, [
            'uid' => $registration_valuer->id,
            'data' => [
                'status' => Registration::STATUS_WAITLIST,
                'obs' => 'editado pelo gestor',
            ],
        ]);

        $this->assertStatus200($request, 'Gestor com @control deve poder editar avaliação já existente de outro avaliador');
    }
}
