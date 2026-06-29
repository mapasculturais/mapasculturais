<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityCounterArgumentPhaseTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RequestFactory,
        UserDirector;

    function testCreateCounterArgumentPhaseForTechnicalEvaluationCreatesContinuousPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $technical_phase = $this->createTechnicalEvaluationPhase($admin->profile);
        $this->createAppealPhase($technical_phase);

        $counter_argument_phase = $this->createCounterArgumentPhase($technical_phase);

        $this->assertNotNull($counter_argument_phase, 'Garantindo que a fase de contrarrazão foi criada');
        $this->assertSame($technical_phase->id, $counter_argument_phase->parent->id, 'Garantindo que a contrarrazão fica abaixo da avaliação técnica');
        $this->assertSame(Opportunity::STATUS_COUNTER_ARGUMENT_PHASE, $counter_argument_phase->status, 'Garantindo status próprio para contrarrazão');
        $this->assertTrue((bool) $counter_argument_phase->isCounterArgumentPhase, 'Garantindo marcação de fase de contrarrazão');
        $this->assertSame('continuous', $counter_argument_phase->evaluationMethodConfiguration->type->id, 'Garantindo resposta por avaliação contínua');
    }

    function testCreateCounterArgumentPhaseRequiresTechnicalEvaluationAndAppealPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $simple_phase = $this->createSimpleEvaluationPhase($admin->profile);
        $this->createAppealPhase($simple_phase);

        $this->createCounterArgumentPhase($simple_phase);
        $simple_phase = $simple_phase->refreshed();

        $this->assertNull($simple_phase->counterArgumentPhase, 'Garantindo que avaliação não técnica não cria contrarrazão');

        $technical_phase = $this->createTechnicalEvaluationPhase($admin->profile);

        $this->createCounterArgumentPhase($technical_phase);
        $technical_phase = $technical_phase->refreshed();

        $this->assertNull($technical_phase->counterArgumentPhase, 'Garantindo que contrarrazão exige recurso configurado primeiro');
    }

    function testCreateCounterArgumentRegistrationAllowsSentTechnicalRegistration(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $technical_phase = $this->createTechnicalEvaluationPhase($admin->profile);
        $this->createAppealPhase($technical_phase);
        $counter_argument_phase = $this->createCounterArgumentPhase($technical_phase);

        $registration = $this->registrationDirector->createSentRegistration($technical_phase, []);
        $registration = $registration->refreshed();

        $counter_argument_registration = $this->createCounterArgumentRegistration($registration);

        $this->assertNotNull($counter_argument_registration, 'Garantindo que a inscrição de contrarrazão foi criada');
        $this->assertSame($counter_argument_phase->id, $counter_argument_registration->opportunity->id, 'Garantindo vínculo com a fase de contrarrazão');
        $this->assertSame($registration->number, $counter_argument_registration->number, 'Garantindo reuso do número da inscrição original');
        $this->assertSame($registration->owner->id, $counter_argument_registration->owner->id, 'Garantindo reuso do proprietário da inscrição original');
    }

    function testCreateCounterArgumentRegistrationRejectsDraftRegistration(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $technical_phase = $this->createTechnicalEvaluationPhase($admin->profile);
        $this->createAppealPhase($technical_phase);
        $this->createCounterArgumentPhase($technical_phase);

        $draft = $this->registrationDirector->createDraftRegistrations($technical_phase, 1)[0]->refreshed();

        $this->createCounterArgumentRegistration($draft);

        $existing = $this->app->repo('Registration')->findOneBy([
            'opportunity' => $technical_phase->counterArgumentPhase,
            'number' => $draft->number,
        ]);

        $this->assertNull($existing, 'Garantindo que rascunho não pode solicitar contrarrazão');
    }

    private function createTechnicalEvaluationPhase(Agent $owner): Opportunity
    {
        return $this->createEvaluationPhase($owner, EvaluationMethods::technical);
    }

    private function createSimpleEvaluationPhase(Agent $owner): Opportunity
    {
        return $this->createEvaluationPhase($owner, EvaluationMethods::simple);
    }

    private function createEvaluationPhase(Agent $owner, EvaluationMethods $evaluation_method): Opportunity
    {
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner, owner_entity: $owner)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase($evaluation_method)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->refresh()
            ->getInstance();

        return $opportunity->evaluationMethodConfiguration->opportunity;
    }

    private function createAppealPhase(Opportunity $opportunity): ?Opportunity
    {
        $this->callOpportunityAction('createAppealPhase', $opportunity->id, ['id' => $opportunity->id]);

        return $opportunity->refreshed()->appealPhase;
    }

    private function createCounterArgumentPhase(Opportunity $opportunity): ?Opportunity
    {
        $this->callOpportunityAction('createCounterArgumentPhase', $opportunity->id, ['id' => $opportunity->id]);

        return $opportunity->refreshed()->counterArgumentPhase;
    }

    private function createCounterArgumentRegistration(Registration $registration): ?Registration
    {
        $this->callOpportunityAction(
            'createCounterArgumentPhaseRegistration',
            $registration->opportunity->id,
            ['registrationId' => $registration->id]
        );

        return $this->app->repo('Registration')->findOneBy([
            'opportunity' => $registration->opportunity->counterArgumentPhase,
            'number' => $registration->number,
        ]);
    }

    private function callOpportunityAction(string $action, int $opportunity_id, array $data): void
    {
        $app = App::i();
        $app->request = $this->requestFactory->mapasPOST('opportunity', $action, [$opportunity_id], $data);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData($data);

        try {
            $controller->callAction('POST', $action, []);
        } catch (Halt) {
        }
    }
}
