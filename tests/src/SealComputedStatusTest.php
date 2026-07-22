<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Unit / integration tests for SealRelation::getComputedStatus() decision table.
 */
class SealComputedStatusTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector;

    /**
     * Creates a seal with a granular field configuration and applies it to a new agent.
     *
     * @param array<string,array> $config
     * @param int $validPeriod
     * @return array{0:Seal,1:Agent}
     */
    protected function createSealAndAgent(array $config, int $validPeriod = 0): array
    {
        $app = App::i();
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = $validPeriod;
        $seal->lockedFieldsConfig = $config;
        $seal->save(true);

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $agent->createSealRelation($seal, true, true, $admin->profile);
        $agent = $agent->refreshed();

        return [$seal, $agent];
    }

    /**
     * Updates the expiry dates of the SealRelationFields for the given seal/agent pair.
     *
     * @param Agent $agent
     * @param Seal $seal
     * @param array<string,string|null> $dates key = field name, value = relative date string or null
     */
    protected function setFieldExpiryDates(Agent $agent, Seal $seal, array $dates): void
    {
        $app = App::i();
        $relation = $this->findRelation($agent, $seal);

        foreach ($relation->getSealRelationFields() as $field) {
            if (!array_key_exists($field->fieldName, $dates)) {
                continue;
            }

            $value = $dates[$field->fieldName];
            $field->expiryDate = $value === null
                ? null
                : new DateTime($value, new DateTimeZone('UTC'));
            $app->em->persist($field);
        }

        $relation->updateComputedStatus();
        $app->em->persist($relation);
        $app->em->flush();
    }

    protected function findRelation(Agent $agent, Seal $seal): \MapasCulturais\Entities\AgentSealRelation
    {
        foreach ($agent->getSealRelations() as $relation) {
            if ($relation->seal->id === $seal->id) {
                return $relation;
            }
        }

        $this->fail('Seal relation not found');
    }

    public function testAllValidFieldsReturnsFullyValid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ]);

        $this->setFieldExpiryDates($agent, $seal, [
            'agent.name' => '+30 days',
            'agent.nomeCompleto' => '+30 days',
        ]);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('fully_valid', $relation->getComputedStatus());
    }

    public function testNonInvalidatorExpiredOnlyReturnsPartiallyValid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
        ]);

        $this->setFieldExpiryDates($agent, $seal, [
            'agent.name' => '-1 day',
            'agent.nomeCompleto' => '+30 days',
        ]);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('partially_valid', $relation->getComputedStatus());
    }

    public function testInvalidatorExpiredReturnsInvalid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ]);

        $this->setFieldExpiryDates($agent, $seal, [
            'agent.name' => '-1 day',
            'agent.nomeCompleto' => '+30 days',
        ]);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('invalid', $relation->getComputedStatus());
    }

    public function testAllExpiredFieldsReturnsInvalid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ]);

        $this->setFieldExpiryDates($agent, $seal, [
            'agent.name' => '-1 day',
            'agent.nomeCompleto' => '-2 days',
        ]);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('invalid', $relation->getComputedStatus());
    }

    public function testNoFieldsConfiguredReturnsFullyValid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([], 0);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('fully_valid', $relation->getComputedStatus());
    }

    public function testLegacyValidSealReturnsFullyValid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([], 12);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('fully_valid', $relation->getComputedStatus());
    }

    public function testLegacyExpiredSealReturnsInvalid(): void
    {
        [$seal, $agent] = $this->createSealAndAgent([], 12);

        $relation = $this->findRelation($agent, $seal);
        $relation->validateDate = new DateTime('-1 year', new DateTimeZone('UTC'));
        $relation->save(true);

        $relation = $this->findRelation($agent, $seal);
        $this->assertSame('invalid', $relation->getComputedStatus());
    }
}
