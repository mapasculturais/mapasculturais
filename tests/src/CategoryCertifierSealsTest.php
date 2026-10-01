<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Opportunities\Module as OpportunitiesModule;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Selos certificadores por categoria (1ª fase) devem ser aplicados quando a
 * inscrição é aprovada em fase de avaliação filha — inclusive com auto-aplicação.
 */
class CategoryCertifierSealsTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use SealDirector;
    use UserDirector;

    private function opportunitiesModule(): OpportunitiesModule
    {
        return App::i()->modules['Opportunities'];
    }

    private function invokePrivate(object $object, string $method, ...$args)
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object, ...$args);
    }

    public function testGetCertifierSealsSourceUsesFirstPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->getInstance();

        $eval_emc = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::documentary)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->save()
            ->getInstance();

        $eval_opportunity = $eval_emc->opportunity;

        $module = $this->opportunitiesModule();
        $source = $this->invokePrivate($module, 'getCertifierSealsSource', $eval_opportunity);

        $this->assertSame($first_phase->id, $source->id);
    }

    public function testShouldApplyCertifierSealsWhenAutoApplicationAllowedOnEvaluationPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->getInstance();

        $app = App::i();
        $first_phase->publishedRegistrations = false;
        $first_phase->isContinuousFlow = false;
        $first_phase->save(true);

        $eval_emc = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::documentary)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->setAutoApplicationAllowed(true)
            ->save()
            ->getInstance();

        $eval_opportunity = $eval_emc->opportunity;
        $eval_opportunity->publishedRegistrations = false;
        $eval_opportunity->save(true);

        $module = $this->opportunitiesModule();
        $should_apply = $this->invokePrivate($module, 'shouldApplyCertifierSealsOnApproval', $eval_opportunity);

        $this->assertTrue($should_apply);
    }

    public function testCategorySealFromFirstPhaseIsAppliedOnEvaluationPhaseApproval(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $category = 'Certidões';

        $first_phase = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setCategories([$category])
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->getInstance();

        $app = App::i();
        $first_phase->publishedRegistrations = false;
        $first_phase->isContinuousFlow = false;
        $first_phase->categorySeals = (object) [$category => [$seal->id]];
        $first_phase->save(true);

        $eval_emc = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::documentary)
            ->setEvaluationPeriod(new ConcurrentEndingAfter())
            ->setAutoApplicationAllowed(true)
            ->save()
            ->getInstance();

        $eval_opportunity = $eval_emc->opportunity;

        $registration_first = $this->registrationDirector->createSentRegistration($first_phase, [
            'category' => $category,
        ]);

        $eval_opportunity->syncRegistrations([$registration_first]);
        $this->processJobs();

        $eval_registration = $app->repo('Registration')->findOneBy([
            'opportunity' => $eval_opportunity,
            'number' => $registration_first->number,
        ]);

        $this->assertInstanceOf(Registration::class, $eval_registration);
        $this->assertSame($category, $eval_registration->category);

        $owner = $eval_registration->owner;
        $this->assertCount(0, $owner->getSealRelations());

        $app->disableAccessControl();
        $eval_registration->setStatusToApproved(true);
        $app->enableAccessControl();

        $owner = $owner->refreshed();
        $relations = $owner->getSealRelations();
        $this->assertCount(1, $relations);
        $this->assertSame($seal->id, $relations[0]->seal->id);
    }
}
