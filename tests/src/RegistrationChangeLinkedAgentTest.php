<?php

namespace Tests;

use MapasCulturais\Entity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Troca de agente vinculado à inscrição por saasSuperAdmin (issue #126).
 */
class RegistrationChangeLinkedAgentTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use RequestFactory;
    use UserDirector;

    public function testSaasSuperAdminCanOpenRegistrationWithOwnerInTrash(): void
    {
        $saas = $this->userDirector->createUser('saasSuperAdmin');
        $owner_user = $this->userDirector->createUser();
        $admin = $this->userDirector->createUser('admin');

        $this->login($admin);
        $opportunity = $this->createOpenOpportunity($admin);
        $registration = $this->registrationDirector->createSentRegistrationForAgent(
            $opportunity,
            $owner_user->profile
        );

        $this->app->disableAccessControl();
        $owner_user->profile->delete(true);
        $this->app->enableAccessControl();
        $this->app->em->clear();

        $this->login($saas);
        $registration = $this->app->repo('Registration')->find($registration->id);

        $this->assertTrue($registration->isOwnerInTrash());
        $this->assertTrue($registration->canUser('view'));

        $json = $registration->jsonSerialize();
        $this->assertNotNull($json['owner']);
        $this->assertEquals(Entity::STATUS_TRASH, (int) $json['owner']->status);
        $this->assertEquals($owner_user->profile->id, $json['owner']->id);
    }

    public function testChangeLinkedAgentPropagatesAcrossPhasesAndRefreshesSnapshot(): void
    {
        $saas = $this->userDirector->createUser('saasSuperAdmin');
        $owner_user = $this->userDirector->createUser();
        $new_owner_user = $this->userDirector->createUser();
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

        $eval_phase = $this->opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter())
                ->save()
                ->getInstance();
        $second_phase = $eval_phase->opportunity;

        $registration = $this->registrationDirector->createSentRegistrationForAgent(
            $first_phase,
            $owner_user->profile
        );
        $number = $registration->number;
        $old_owner_name = $registration->agentsData['owner']['name'] ?? null;

        // Simula família de fases com o mesmo number (padrão findBy number)
        $this->app->disableAccessControl();
        $phase2_registration = new Registration();
        $phase2_registration->opportunity = $second_phase;
        $phase2_registration->owner = $owner_user->profile;
        $phase2_registration->number = $number;
        $phase2_registration->status = Registration::STATUS_DRAFT;
        $phase2_registration->save(true);
        $this->app->enableAccessControl();

        $this->app->disableAccessControl();
        $owner_user->profile->delete(true);
        $this->app->enableAccessControl();
        $this->app->em->clear();

        $this->login($saas);
        $registration = $this->app->repo('Registration')->find($registration->id);

        $request = $this->requestFactory->POST(
            'registration',
            'changeLinkedAgent',
            [$registration->id],
            ['ownerId' => $new_owner_user->profile->id]
        );
        $this->assertStatus200($request);

        $this->processPCache();
        $this->app->em->clear();

        $family = $this->app->repo('Registration')->findBy(['number' => $number]);
        $this->assertCount(2, $family);

        foreach ($family as $reg) {
            $this->assertEquals(
                $new_owner_user->profile->id,
                $reg->owner->id,
                "Garantindo troca do owner na inscrição {$reg->id}"
            );
            $this->assertFalse($reg->isOwnerInTrash());

            if ($reg->status >= Registration::STATUS_SENT) {
                $agents_data = $reg->agentsData;
                $this->assertEquals(
                    $new_owner_user->profile->name,
                    $agents_data['owner']['name'] ?? null,
                    "Garantindo regeneração de agents_data na inscrição {$reg->id}"
                );
                $this->assertNotEquals($old_owner_name, $agents_data['owner']['name'] ?? null);
            } else {
                // rascunho: snapshot só no envio
                $this->assertEmpty($reg->agentsData ?: []);
            }
        }
    }

    public function testChangeLinkedAgentRejectsInactiveDestination(): void
    {
        $saas = $this->userDirector->createUser('saasSuperAdmin');
        $owner_user = $this->userDirector->createUser();
        $inactive_user = $this->userDirector->createUser();
        $admin = $this->userDirector->createUser('admin');

        $this->login($admin);
        $opportunity = $this->createOpenOpportunity($admin);
        $registration = $this->registrationDirector->createSentRegistrationForAgent(
            $opportunity,
            $owner_user->profile
        );

        $this->app->disableAccessControl();
        $inactive_user->profile->delete(true);
        $this->app->enableAccessControl();

        $this->login($saas);
        $request = $this->requestFactory->POST(
            'registration',
            'changeLinkedAgent',
            [$registration->id],
            ['ownerId' => $inactive_user->profile->id]
        );
        $this->assertStatus400($request);

        $this->app->em->clear();
        $registration = $this->app->repo('Registration')->find($registration->id);
        $this->assertEquals($owner_user->profile->id, $registration->owner->id);
    }

    public function testChangeLinkedAgentForbiddenForNonSaasSuperAdmin(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $owner_user = $this->userDirector->createUser();
        $new_owner_user = $this->userDirector->createUser();

        $this->login($admin);
        $opportunity = $this->createOpenOpportunity($admin);
        $registration = $this->registrationDirector->createSentRegistrationForAgent(
            $opportunity,
            $owner_user->profile
        );

        $request = $this->requestFactory->POST(
            'registration',
            'changeLinkedAgent',
            [$registration->id],
            ['ownerId' => $new_owner_user->profile->id]
        );
        $this->assertStatus403($request);
    }

    public function testChangeLinkedAgentRequiresAuthentication(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $owner_user = $this->userDirector->createUser();
        $new_owner_user = $this->userDirector->createUser();

        $this->login($admin);
        $opportunity = $this->createOpenOpportunity($admin);
        $registration = $this->registrationDirector->createSentRegistrationForAgent(
            $opportunity,
            $owner_user->profile
        );

        $this->app->auth->logout();

        $request = $this->requestFactory->POST(
            'registration',
            'changeLinkedAgent',
            [$registration->id],
            ['ownerId' => $new_owner_user->profile->id]
        );
        $this->assertStatus401($request);
    }

    protected function createOpenOpportunity($admin)
    {
        return $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open())
                ->done()
            ->save()
            ->getInstance();
    }
}
