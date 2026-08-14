<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Regression tests for legacy seals without per-field expiration.
 */
class SealLegacyRegressionTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector;

    protected function createLegacySeal(): Seal
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = $this->sealDirector->createSeal($admin->profile);
        $seal->validPeriod = 0;
        // Legacy path: only locked_fields is populated. The dual-write keeps
        // locked_fields_config in sync, preserving the blocking behavior.
        $seal->lockedFields = ['agent.name'];
        $seal->save(true);

        return $seal;
    }

    public function testLegacySealBlocksConfiguredFields(): void
    {
        $seal = $this->createLegacySeal();

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $agent->createSealRelation($seal, true, true, App::i()->user->profile);
        $agent = $agent->refreshed();

        $this->login($owner);

        try {
            $agent->name = 'Changed by owner ' . uniqid();
            $agent->save(true);
            $this->fail('Legacy seal should still block the configured field for non-admins');
        } catch (PermissionDenied $e) {
            $this->assertStringContainsString('locked field: name', $e->getMessage());
        }
    }

    public function testLegacySealRemainsFullyValid(): void
    {
        $seal = $this->createLegacySeal();

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $relation = $agent->createSealRelation($seal, true, true, App::i()->user->profile);

        $this->assertSame('fully_valid', $relation->getComputedStatus());
        $this->assertSame('fully_valid', $relation->computedStatus);
    }

    public function testLegacySealDoesNotGenerateNotifications(): void
    {
        $seal = $this->createLegacySeal();

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $agent->createSealRelation($seal, true, true, App::i()->user->profile);

        $before = count(App::i()->repo(Notification::class)->findBy(['user' => $owner]));

        $this->processJobs(as_date: '2100-01-01 00:00', number_of_jobs: 1);

        $after = count(App::i()->repo(Notification::class)->findBy(['user' => $owner]));
        $this->assertSame($before, $after);
    }

    public function testLegacySealSurvivesReloadAndStillBlocks(): void
    {
        $seal = $this->createLegacySeal();

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $agent->createSealRelation($seal, true, true, App::i()->user->profile);

        $agent = App::i()->repo('Agent')->find($agent->id);

        $this->login($owner);

        try {
            $agent->name = 'Changed after reload ' . uniqid();
            $agent->save(true);
            $this->fail('Legacy seal should still block the field after reload');
        } catch (PermissionDenied $e) {
            $this->assertTrue(true);
        }
    }

    public function testLegacySealFieldsHaveNoExpiryDate(): void
    {
        $seal = $this->createLegacySeal();

        $owner = $this->userDirector->createUser();
        $agent = $owner->profile;
        $relation = $agent->createSealRelation($seal, true, true, App::i()->user->profile);

        foreach ($relation->getSealRelationFields() as $field) {
            $this->assertNull($field->expiryDate);
            $this->assertSame('no_expiration', $field->getFieldStatus());
        }
    }
}
