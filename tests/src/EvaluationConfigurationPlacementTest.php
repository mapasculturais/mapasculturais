<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class EvaluationConfigurationPlacementTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        UserDirector;

    function testConfigurationCreatedOnRootMovesToLastPhaseWithoutConfiguration(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $root = $builder->getInstance();

        $dataCollectionPhase = $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->getInstance();

        $configuration = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $this->assertSame($dataCollectionPhase->id, $configuration->opportunity->id);
        $this->assertNull($this->configurationOf($root));
    }

    function testConfigurationCreatedWithAllPhasesOccupiedGetsShelterPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $root = $builder->getInstance();

        $firstConfiguration = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $phasesBefore = count($this->phasesOf($root));

        $secondConfiguration = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $shelterPhase = $secondConfiguration->opportunity;

        $this->assertSame($root->id, $firstConfiguration->opportunity->id);
        $this->assertNotSame($root->id, $shelterPhase->id);
        $this->assertSame($root->id, $shelterPhase->parent->id);
        $this->assertFalse((bool) $shelterPhase->isDataCollection);
        $this->assertTrue((bool) $shelterPhase->isOpportunityPhase);
        $this->assertCount($phasesBefore + 1, $this->phasesOf($root));
    }

    function testAppealPhaseConfigurationStaysOnAppealPhase(): void
    {
        $app = $this->app;
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
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->refresh()
            ->getInstance();

        $opportunityId = $opportunity->id;
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'createAppealPhase', [$opportunityId], ['id' => $opportunityId]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $opportunityId]);

        try {
            $controller->callAction('POST', 'createAppealPhase', []);
        } catch (Halt) {
        }

        $app->em->clear();

        $opportunity = $app->repo('Opportunity')->find($opportunityId);
        $appealPhase = $opportunity->appealPhase;

        $this->assertNotNull($appealPhase);

        $configuration = $this->configurationOf($appealPhase);

        $this->assertNotNull($configuration);
        $this->assertSame('continuous', $configuration->type->id);
    }

    function testConfigurationCreatedDirectlyOnPhaseStaysOnPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $root = $builder->getInstance();

        $targetPhase = $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->getInstance();

        $lastFreePhase = $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->getInstance();

        $phaseBuilder = new EvaluationPhaseBuilder($this->opportunityBuilder);
        $phaseBuilder->reset($targetPhase, EvaluationMethods::simple);
        $configuration = $phaseBuilder
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        $this->assertSame($targetPhase->id, $configuration->opportunity->id);
        $this->assertNull($this->configurationOf($lastFreePhase));
        $this->assertNull($this->configurationOf($root));
    }

    private function configurationOf(Opportunity $opportunity)
    {
        return $this->app->repo('EvaluationMethodConfiguration')->findOneBy(['opportunity' => $opportunity]);
    }

    private function phasesOf(Opportunity $root): array
    {
        return $this->app->repo('Opportunity')->findBy(['parent' => $root]);
    }
}
