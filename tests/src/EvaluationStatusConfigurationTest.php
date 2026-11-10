<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\User;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\Faker;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Garante que os status oferecidos ao avaliador (e aceitos no envio da avaliação)
 * seguem a configuração de status da fase (statusLabels da oportunidade).
 */
class EvaluationStatusConfigurationTest extends Abstract\TestCase
{
    use RequestFactory,
        UserDirector,
        Faker,
        OpportunityBuilder,
        RegistrationDirector;

    function testEvaluatorStatusesFollowMethodDefaultsWhenPhaseIsNotConfigured()
    {
        foreach (['simple', 'continuous'] as $type) {
            [$opportunity] = $this->createEvaluationPhase($type);

            $emc = $opportunity->evaluationMethodConfiguration;
            $expected = $emc->defaultStatuses;
            unset($expected[Registration::STATUS_DRAFT], $expected[Registration::STATUS_SENT]);

            $this->assertEquals(
                $expected,
                $emc->evaluationMethod->getEvaluatorStatuses($emc),
                "Garantindo que, sem configuração de status na fase, o avaliador da avaliação {$type} recebe os status padrão do método (sem Rascunho e Pendente)"
            );
        }
    }

    function testContinuousDefaultStatusesAreDeniedRejectedAndApproved()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous');

        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertSame(
            [Registration::STATUS_INVALID, Registration::STATUS_NOTAPPROVED, Registration::STATUS_APPROVED],
            array_keys($emc->evaluationMethod->getEvaluatorStatuses($emc)),
            'Garantindo que a avaliação contínua sem configuração continua oferecendo Negado, Indeferido e Deferido'
        );
    }

    function testEvaluatorStatusesExcludeDisabledStatusesAndUseConfiguredLabels()
    {
        [$opportunity] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido pela comissão',
            '10' => 'Deferido',
        ]);

        $emc = $opportunity->evaluationMethodConfiguration;

        $this->assertEquals(
            [3 => 'Indeferido pela comissão', 10 => 'Deferido'],
            $emc->evaluationMethod->getEvaluatorStatuses($emc),
            'Garantindo que status desabilitados não são oferecidos, Rascunho/Pendente são ignorados e os rótulos configurados são usados'
        );
    }

    function testContinuousEvaluationFormOnlyListsEnabledStatuses()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '10' => 'Deferido pela comissão',
        ]);

        $this->login($valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo que o avaliador acessa a tela de avaliação contínua');

        $config = $this->app->view->jsObject['config'];

        $this->assertEquals(
            [
                ['value' => 3, 'label' => 'Indeferido'],
                ['value' => 10, 'label' => 'Deferido pela comissão'],
            ],
            $config['continuousEvaluationForm']['statusList'],
            'Garantindo que o formulário da avaliação contínua não oferece o status Negado desabilitado na fase'
        );

        $this->assertEquals(
            [3 => 'Indeferido', 10 => 'Deferido pela comissão'],
            $config['appealPhaseChatStatuses'],
            'Garantindo que o chat do recurso oferece apenas os status habilitados na fase'
        );
    }

    function testSimpleEvaluationFormOnlyListsEnabledStatuses()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('simple', [
            '0' => 'Rascunho',
            '1' => 'Pendente',
            '2' => 'Inválida',
            '3' => 'Não selecionada',
            '10' => 'Selecionada',
        ]);

        $this->login($valuer);

        $request = $this->requestFactory->GET('registration', 'evaluation', [$registration->id]);
        $this->assertStatus200($request, 'Garantindo que o avaliador acessa a tela de avaliação simplificada');

        $this->assertEquals(
            [
                ['value' => '2', 'label' => 'Inválida'],
                ['value' => '3', 'label' => 'Não selecionada'],
                ['value' => '10', 'label' => 'Selecionada'],
            ],
            $this->app->view->jsObject['config']['simpleEvaluationForm']['statusList'],
            'Garantindo que o formulário da avaliação simplificada não oferece o status Suplente desabilitado na fase'
        );
    }

    function testContinuousEvaluationRejectsDisabledStatus()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('continuous', [
            '0' => 'Rascunho',
            '1' => 'Aguardando resposta',
            '3' => 'Indeferido',
            '10' => 'Deferido',
        ]);

        $this->login($valuer);

        $this->assertStatus400(
            $this->sendEvaluation($registration, Registration::STATUS_INVALID),
            'Garantindo que a avaliação contínua não pode ser enviada com o status Negado desabilitado na fase'
        );

        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_NOTAPPROVED),
            'Garantindo que a avaliação contínua pode ser enviada com um status habilitado na fase'
        );
    }

    function testSimpleEvaluationRejectsDisabledStatus()
    {
        [, $registration, $valuer] = $this->createEvaluationPhase('simple', [
            '0' => 'Rascunho',
            '1' => 'Pendente',
            '2' => 'Inválida',
            '3' => 'Não selecionada',
            '10' => 'Selecionada',
        ]);

        $this->login($valuer);

        $this->assertStatus400(
            $this->sendEvaluation($registration, Registration::STATUS_WAITLIST),
            'Garantindo que a avaliação simplificada não pode ser enviada com o status Suplente desabilitado na fase'
        );

        $this->assertStatus200(
            $this->sendEvaluation($registration, Registration::STATUS_APPROVED),
            'Garantindo que a avaliação simplificada pode ser enviada com um status habilitado na fase'
        );
    }

    function testEvaluationRejectsDraftAndPendingStatuses()
    {
        foreach (['simple', 'continuous'] as $type) {
            [, $registration, $valuer] = $this->createEvaluationPhase($type);

            $this->login($valuer);

            foreach ([Registration::STATUS_DRAFT, Registration::STATUS_SENT] as $status) {
                $this->assertStatus400(
                    $this->sendEvaluation($registration, $status),
                    "Garantindo que a avaliação {$type} não pode ser enviada com o status {$status}"
                );
            }
        }
    }

    /**
     * Cria uma fase de avaliação com uma inscrição enviada e distribuída a um avaliador
     *
     * @return array{Opportunity, Registration, User}
     */
    private function createEvaluationPhase(string $type, ?array $status_labels = null): array
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
                ->addValuers(1, 'committee 1')
                ->done()
            ->getInstance();

        if ($type !== 'simple') {
            $opportunity->evaluationMethodConfiguration->type = $type;
            $opportunity->evaluationMethodConfiguration->save(true);
        }

        if ($status_labels !== null) {
            $opportunity->statusLabels = $status_labels;
            $opportunity->save(true);
        }

        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        $registration = $registration->refreshed();
        $valuer_user_id = array_keys($registration->valuers)[0];
        $valuer = $this->app->repo('User')->find($valuer_user_id);

        return [$opportunity->refreshed(), $registration, $valuer];
    }

    private function sendEvaluation(Registration $registration, int $status)
    {
        return $this->requestFactory->POST(
            controller_id: 'registration',
            action: 'saveEvaluation',
            url_params: [
                'id' => $registration->id,
                'status' => 'evaluated'
            ],
            payload: [
                'data' => [
                    'status' => $status,
                    'obs' => $this->faker->text()
                ]
            ]
        );
    }
}
