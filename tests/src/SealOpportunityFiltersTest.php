<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Integration tests for opportunity registration filters by sealStatus.
 */
class SealOpportunityFiltersTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector, OpportunityBuilder, RegistrationDirector;

    protected Opportunity $opportunity;
    protected \MapasCulturais\Controllers\Opportunity $controller;
    protected Agent $agentFullyValid;
    protected Agent $agentPartiallyValid;
    protected Agent $agentInvalid;
    protected Agent $agentWithoutSeal;
    protected array $registrations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->agentDirector->createIndividual($admin);

        /** @var Opportunity */
        $this->opportunity = $this->opportunityBuilder
            ->reset(owner: $owner, owner_entity: $owner)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('step')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $seal = $this->createFilterSeal();

        $this->agentFullyValid = $this->createAgentWithSealStatus($seal, 'fully_valid');
        $this->agentPartiallyValid = $this->createAgentWithSealStatus($seal, 'partially_valid');
        $this->agentInvalid = $this->createAgentWithSealStatus($seal, 'invalid');
        $this->agentWithoutSeal = $this->userDirector->createUser()->profile;

        foreach ([
            'fully_valid' => $this->agentFullyValid,
            'partially_valid' => $this->agentPartiallyValid,
            'invalid' => $this->agentInvalid,
            'none' => $this->agentWithoutSeal,
        ] as $label => $agent) {
            $registration = $this->registrationDirector->createSentRegistrationForAgent($this->opportunity, $agent);
            $this->registrations[$label] = $registration;
        }

        $this->processPCache();

        /** @var \MapasCulturais\Controllers\Opportunity */
        $this->controller = App::i()->controller('opportunity');
    }

    protected function createFilterSeal(): Seal
    {
        $admin = $this->userDirector->createUser('admin');
        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = 0;
        $seal->sensitive = false;
        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ];
        $seal->save(true);

        return $seal;
    }

    protected function createAgentWithSealStatus(Seal $seal, string $status): Agent
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $relation = $agent->createSealRelation($seal, true, true, $admin->profile);
        $agent = $agent->refreshed();

        $dates = match ($status) {
            'fully_valid' => ['agent.name' => '+30 days', 'agent.nomeCompleto' => '+30 days'],
            'partially_valid' => ['agent.name' => '+30 days', 'agent.nomeCompleto' => '-1 day'],
            'invalid' => ['agent.name' => '-1 day', 'agent.nomeCompleto' => '+30 days'],
            default => [],
        };

        foreach ($relation->getSealRelationFields() as $field) {
            if (!isset($dates[$field->fieldName])) {
                continue;
            }
            $field->expiryDate = new DateTime($dates[$field->fieldName], new DateTimeZone('UTC'));
            App::i()->em->persist($field);
        }

        $relation->updateComputedStatus();
        App::i()->em->persist($relation);
        App::i()->em->flush();

        return $agent->refreshed();
    }

    protected function findRegistrationIdsBySealStatus(string $status): array
    {
        $result = $this->controller->apiFindRegistrations($this->opportunity, [
            '@select' => 'id',
            'sealStatus' => $status,
        ]);

        return array_map(fn($r) => $r['id'], $result->registrations);
    }

    public function testFilterFullyValid(): void
    {
        $ids = $this->findRegistrationIdsBySealStatus('fully_valid');
        $this->assertContains($this->registrations['fully_valid']->id, $ids);
        $this->assertNotContains($this->registrations['partially_valid']->id, $ids);
        $this->assertNotContains($this->registrations['invalid']->id, $ids);
        $this->assertNotContains($this->registrations['none']->id, $ids);
    }

    public function testFilterPartiallyValid(): void
    {
        $ids = $this->findRegistrationIdsBySealStatus('partially_valid');
        $this->assertNotContains($this->registrations['fully_valid']->id, $ids);
        $this->assertContains($this->registrations['partially_valid']->id, $ids);
        $this->assertNotContains($this->registrations['invalid']->id, $ids);
        $this->assertNotContains($this->registrations['none']->id, $ids);
    }

    public function testFilterInvalid(): void
    {
        $ids = $this->findRegistrationIdsBySealStatus('invalid');
        $this->assertNotContains($this->registrations['fully_valid']->id, $ids);
        $this->assertNotContains($this->registrations['partially_valid']->id, $ids);
        $this->assertContains($this->registrations['invalid']->id, $ids);
        $this->assertNotContains($this->registrations['none']->id, $ids);
    }

    public function testFilterValidAliasIncludesFullyAndPartiallyValid(): void
    {
        $ids = $this->findRegistrationIdsBySealStatus('valid');
        $this->assertContains($this->registrations['fully_valid']->id, $ids);
        $this->assertContains($this->registrations['partially_valid']->id, $ids);
        $this->assertNotContains($this->registrations['invalid']->id, $ids);
        $this->assertNotContains($this->registrations['none']->id, $ids);
    }
}
