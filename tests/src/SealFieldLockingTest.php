<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Integration tests for per-field locking via EntitySealRelation::isFieldLocked() and MagicSetter.
 */
class SealFieldLockingTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector;

    /**
     * @return array{0:Seal,1:Agent,2:\MapasCulturais\Entities\AgentSealRelation}
     */
    protected function createLockedAgent(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = 0;
        $seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ];
        $seal->save(true);

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $relation = $agent->createSealRelation($seal, true, true, $admin->profile);
        $agent = $agent->refreshed();

        return [$seal, $agent, $relation];
    }

    protected function getNameField(Agent $agent, Seal $seal): \MapasCulturais\Entities\SealRelationField
    {
        foreach ($agent->getSealRelations() as $relation) {
            if ($relation->seal->id !== $seal->id) {
                continue;
            }
            foreach ($relation->getSealRelationFields() as $field) {
                if ($field->fieldName === 'agent.name') {
                    return $field;
                }
            }
        }

        $this->fail('Field agent.name not found in seal relation');
    }

    protected function setFieldDate(Agent $agent, Seal $seal, ?string $dateSpec): void
    {
        $app = App::i();
        $field = $this->getNameField($agent, $seal);
        $field->expiryDate = $dateSpec === null
            ? null
            : new DateTime($dateSpec, new DateTimeZone('UTC'));
        $app->em->persist($field);
        $app->em->flush();
    }

    protected function assertFieldLocked(Agent $agent, string $message = ''): void
    {
        $this->expectException(PermissionDenied::class);
        $agent->name = 'Changed by non-admin ' . uniqid();
        $agent->save(true);

        if ($message) {
            $this->fail($message);
        }
    }

    public function testValidFieldIsLockedForNonAdmin(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, '+30 days');
        $agent = $agent->refreshed();

        $owner = $agent->owner->user;
        $this->login($owner);

        $this->assertFieldLocked($agent, 'Expected valid field to be locked for the entity owner');
    }

    public function testNoExpirationFieldIsLockedForNonAdmin(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, null);
        $agent = $agent->refreshed();

        $owner = $agent->owner->user;
        $this->login($owner);

        $this->assertFieldLocked($agent, 'Expected no_expiration field to be locked for the entity owner');
    }

    public function testExpiredFieldIsUnlockedForNonAdmin(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, '-1 day');
        $agent = $agent->refreshed();

        $owner = $agent->owner->user;
        $this->login($owner);

        $newName = 'Unlocked after expiry ' . uniqid();
        $agent->name = $newName;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame($newName, $agent->name);
    }

    public function testAboutToExpireFieldIsUnlockedForNonAdmin(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, '+3 days');
        $agent = $agent->refreshed();

        $owner = $agent->owner->user;
        $this->login($owner);

        $newName = 'Unlocked before expiry ' . uniqid();
        $agent->name = $newName;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame($newName, $agent->name);
    }

    public function testAdminCanEditLockedField(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, '+30 days');
        $agent = $agent->refreshed();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $newName = 'Edited by admin ' . uniqid();
        $agent->name = $newName;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame($newName, $agent->name);
    }

    public function testLockingDoesNotRelyOnCache(): void
    {
        [$seal, $agent] = $this->createLockedAgent();
        $this->setFieldDate($agent, $seal, '+30 days');
        $agent = $agent->refreshed();

        // Poison the cache with a stale "valid" entry for the field.
        $app = App::i();
        $field = $this->getNameField($agent, $seal);
        $cacheKey = "seal_field_status_{$field->id}";
        $app->rcache->save($cacheKey, 'valid');

        // Move the field to expired directly in the database.
        $app->conn->executeQuery(
            'UPDATE seal_relation_field SET expiry_date = :date WHERE id = :id',
            ['date' => (new DateTime('-1 day', new DateTimeZone('UTC')))->format('Y-m-d'), 'id' => $field->id]
        );
        $app->em->clear();

        $agent = $agent->refreshed();
        $owner = $agent->owner->user;
        $this->login($owner);

        // If the implementation were reading the cache, the field would still be
        // considered valid and the write would be denied.
        $newName = 'Unlocked despite stale cache ' . uniqid();
        $agent->name = $newName;
        $agent->save(true);
        $agent = $agent->refreshed();

        $this->assertSame($newName, $agent->name);
    }
}
