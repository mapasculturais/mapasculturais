<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class AppealTwoStagePublishTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function createEvaluatedOpportunityScenario(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->addValuer('Comissão', 'Avaliador 1', $admin->profile)
                    ->done()
                ->done();

        /** @var Opportunity $opportunity */
        $opportunity = $evaluation_phase_builder->getInstance();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        return compact('admin', 'opportunity', 'registration');
    }

    private function enableTwoStagePublish(): void
    {
        $_ENV['APPEAL_TWO_STAGE_PUBLISH'] = 'true';
    }

    private function disableTwoStagePublish(): void
    {
        unset($_ENV['APPEAL_TWO_STAGE_PUBLISH']);
        putenv('APPEAL_TWO_STAGE_PUBLISH');
    }

    function testFlagOffBlocksPreliminaryPublish(): void
    {
        $this->disableTwoStagePublish();

        ['opportunity' => $opportunity] = $this->createEvaluatedOpportunityScenario();

        $this->expectException(PermissionDenied::class);
        $opportunity->publishPreliminaryRegistrations();
    }

    function testFlagOffPreservesFinalPublish(): void
    {
        $this->disableTwoStagePublish();

        ['opportunity' => $opportunity] = $this->createEvaluatedOpportunityScenario();

        $app = App::i();
        $app->disableAccessControl();
        $opportunity->publishRegistrations();
        $app->enableAccessControl();

        $opportunity->refresh();
        $this->assertTrue($opportunity->publishedRegistrations);
        $this->assertTrue($opportunity->areRegistrationResultsPublished());
        $this->assertFalse((bool) $opportunity->publishedPreliminaryRegistrations);
    }

    function testPreliminaryThenFinalPublish(): void
    {
        $this->enableTwoStagePublish();

        ['admin' => $admin, 'opportunity' => $opportunity, 'registration' => $registration] = $this->createEvaluatedOpportunityScenario();

        $proponent = $registration->owner->user;

        $this->assertFalse($opportunity->areRegistrationResultsPublished());

        $this->login($proponent);
        $this->assertFalse($registration->canUser('viewConsolidatedResult'));

        $this->login($admin);
        $opportunity->publishPreliminaryRegistrations();
        // Métodos de lote terminam com em->clear() e desanexam a instância
        // (padrão do publishRegistrations); re-buscar antes de encadear,
        // como faz o AppealPreliminarySnapshotTest.
        $opportunity = App::i()->repo('Opportunity')->find($opportunity->id);

        $this->assertTrue((bool) $opportunity->publishedPreliminaryRegistrations);
        $this->assertFalse($opportunity->publishedRegistrations);
        $this->assertTrue($opportunity->areRegistrationResultsPublished());

        $registration = App::i()->repo('Registration')->find($registration->id);
        $this->login($proponent);
        $this->assertTrue($registration->canUser('viewConsolidatedResult'));

        $this->login($admin);
        $opportunity->publishRegistrations();
        $opportunity = App::i()->repo('Opportunity')->find($opportunity->id);

        $this->assertTrue($opportunity->publishedRegistrations);
        $this->assertTrue((bool) $opportunity->publishedPreliminaryRegistrations);
        $this->assertTrue($opportunity->areRegistrationResultsPublished());
    }

    function testPreliminaryPublishExposesEvaluationDetails(): void
    {
        $this->enableTwoStagePublish();

        ['opportunity' => $opportunity, 'registration' => $registration] = $this->createEvaluatedOpportunityScenario();

        $evaluation_configuration = $opportunity->evaluationMethodConfiguration;
        $evaluation_configuration->publishEvaluationDetails = true;
        $evaluation_configuration->save(true);

        $data = App::i()->repo('Registration')->find($registration->id)->jsonSerialize();
        $this->assertArrayNotHasKey('consolidatedDetails', $data);

        $opportunity->publishPreliminaryRegistrations();

        $registration = App::i()->repo('Registration')->find($registration->id);
        $this->assertFalse($registration->opportunity->publishedRegistrations);

        $data = $registration->jsonSerialize();
        $this->assertArrayHasKey('consolidatedDetails', $data);
        $this->assertArrayHasKey('evaluationsDetails', $data);
    }

    function testUnpublishPreliminaryDoesNotTouchFinal(): void
    {
        $this->enableTwoStagePublish();

        ['opportunity' => $opportunity] = $this->createEvaluatedOpportunityScenario();

        $opportunity->publishPreliminaryRegistrations();
        $opportunity = App::i()->repo('Opportunity')->find($opportunity->id);
        $opportunity->publishRegistrations();
        $opportunity = App::i()->repo('Opportunity')->find($opportunity->id);

        $opportunity->unPublishPreliminaryRegistrations();
        $opportunity = App::i()->repo('Opportunity')->find($opportunity->id);

        $this->assertFalse((bool) $opportunity->publishedPreliminaryRegistrations);
        $this->assertTrue($opportunity->publishedRegistrations);
        $this->assertTrue($opportunity->areRegistrationResultsPublished());
    }
}
