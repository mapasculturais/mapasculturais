<?php

namespace Tests;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Integration/API tests for LGPD-sensitive seal visibility.
 */
class SealLgpdVisibilityTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector, OpportunityBuilder, RegistrationDirector;

    protected function createSensitiveSealAndAgent(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = 0;
        $seal->sensitive = true;
        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ];
        $seal->save(true);

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $relation = $agent->createSealRelation($seal, true, true, $admin->profile);
        $agent = $agent->refreshed();

        $this->processPCache();

        return [$seal, $agent, $relation, $owner];
    }

    protected function countSealsInAgentApi(Agent $agent, ?User $user = null): int
    {
        if ($user) {
            $this->login($user);
        } else {
            $this->logout();
        }

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,seals',
            'id' => API::EQ($agent->id),
        ]);
        $result = $query->find();

        return count($result[0]['seals'] ?? []);
    }

    protected function createOpportunityWithManager(Agent $ownerAgent): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $ownerAgent, owner_entity: $ownerAgent)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('step')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $manager = $this->userDirector->createUser();
        $ownerAgent->createAgentRelation($manager->profile, 'admin', true);
        $this->processPCache();

        return [$opportunity, $manager];
    }

    public function testSensitiveSealHiddenFromPublic(): void
    {
        [, $agent] = $this->createSensitiveSealAndAgent();
        $this->assertSame(0, $this->countSealsInAgentApi($agent, null));
    }

    public function testSensitiveSealHiddenFromAuthenticatedNonOwner(): void
    {
        [, $agent] = $this->createSensitiveSealAndAgent();
        $other = $this->userDirector->createUser();
        $this->assertSame(0, $this->countSealsInAgentApi($agent, $other));
    }

    public function testSensitiveSealVisibleToOwner(): void
    {
        [, $agent, , $owner] = $this->createSensitiveSealAndAgent();
        $this->assertSame(1, $this->countSealsInAgentApi($agent, $owner));
    }

    public function testSensitiveSealVisibleToAdmin(): void
    {
        [, $agent] = $this->createSensitiveSealAndAgent();
        $admin = $this->userDirector->createUser('admin');
        $this->assertSame(1, $this->countSealsInAgentApi($agent, $admin));
    }

    public function testSensitiveSealVisibleToOpportunityManagerInDirectCheck(): void
    {
        [$seal, $agent, $relation, $owner] = $this->createSensitiveSealAndAgent();
        [$opportunity, $manager] = $this->createOpportunityWithManager($agent);

        // The entity-level permission helper explicitly accepts an opportunity context.
        $this->assertTrue(
            $relation->canUserViewSensitiveData($manager, $opportunity),
            'Manager with @control on the opportunity should view the sensitive seal in that context'
        );
    }

    public function testSensitiveSealJsonSerializeHidesDataForUnauthorizedUsers(): void
    {
        [$seal, , $relation] = $this->createSensitiveSealAndAgent();
        $other = $this->userDirector->createUser();
        $this->login($other);

        $payload = $relation->jsonSerialize();

        $this->assertSame('hidden', $payload['computedStatus']);
        $this->assertSame('[Selo oculto]', $payload['seal']['name']);
        $this->assertEmpty($payload['fields']);
        $this->assertNotEquals($seal->name, $payload['seal']['name']);
    }

    public function testSensitiveSealJsonSerializeRevealsDataForOwner(): void
    {
        [$seal, , $relation, $owner] = $this->createSensitiveSealAndAgent();
        $this->login($owner);

        $payload = $relation->jsonSerialize();

        $this->assertNotSame('hidden', $payload['computedStatus']);
        $this->assertSame($seal->name, $payload['seal']->name);
        $this->assertNotEmpty($payload['fields']);
    }

    public function testSensitiveSealIncludedInRegistrationFilterForManager(): void
    {
        [$seal, $agent, , $owner] = $this->createSensitiveSealAndAgent();
        [$opportunity, $manager] = $this->createOpportunityWithManager($agent);

        $this->login($admin = $this->userDirector->createUser('admin'));
        $registration = $this->registrationDirector->createSentRegistrationForAgent($opportunity, $agent);

        $this->processPCache();

        /** @var \MapasCulturais\Controllers\Opportunity */
        $controller = App::i()->controller('opportunity');
        $this->login($manager);

        $result = $controller->apiFindRegistrations($opportunity, [
            '@select' => 'id',
            'sealStatus' => 'valid',
        ]);

        $ids = array_map(fn($r) => $r['id'], $result->registrations);
        $this->assertContains($registration->id, $ids);
    }

    public function testSensitiveSealExcludedFromRegistrationFilterForNonManager(): void
    {
        [$seal, $agent, , $owner] = $this->createSensitiveSealAndAgent();
        [$opportunity, $manager] = $this->createOpportunityWithManager($agent);

        $this->login($admin = $this->userDirector->createUser('admin'));
        $registration = $this->registrationDirector->createSentRegistrationForAgent($opportunity, $agent);

        $this->processPCache();

        /** @var \MapasCulturais\Controllers\Opportunity */
        $controller = App::i()->controller('opportunity');
        $other = $this->userDirector->createUser();
        $this->login($other);

        $result = $controller->apiFindRegistrations($opportunity, [
            '@select' => 'id',
            'sealStatus' => 'valid',
        ]);

        $ids = array_map(fn($r) => $r['id'], $result->registrations);
        $this->assertNotContains($registration->id, $ids);
    }

    public function testSensitiveSealVisibleToManagerInBulkEntityLists(): void
    {
        [$seal, $agent, , $owner] = $this->createSensitiveSealAndAgent();
        [, $manager] = $this->createOpportunityWithManager($agent);

        $this->assertSame(
            1,
            $this->countSealsInAgentApi($agent, $manager),
            'Opportunity managers with @control on a linked opportunity should see sensitive seals in bulk entity lists'
        );
    }
}
