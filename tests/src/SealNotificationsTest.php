<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Seal;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\SealDirector;
use Tests\Traits\UserDirector;

/**
 * Integration tests for NotifySealExpirations job idempotency and recipient rules.
 */
class SealNotificationsTest extends TestCase
{
    use UserDirector, AgentDirector, SealDirector;

    protected Seal $seal;
    protected \MapasCulturais\Entities\Agent $agent;
    protected \MapasCulturais\Entities\User $owner;
    protected \MapasCulturais\Entities\AgentSealRelation $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->seal = $this->sealDirector->createSeal($admin->profile);
        $this->seal->validPeriod = 0;
        $this->seal->sensitive = false;
        $this->seal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => true],
            'agent.nomeCompleto' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
            'agent.documento' => ['hasExpiry' => false, 'periodValue' => null, 'periodUnit' => null, 'isInvalidator' => false],
        ];
        $this->seal->save(true);

        $this->owner = $this->userDirector->createUser();
        $this->agent = $this->owner->profile;
        $this->relation = $this->agent->createSealRelation($this->seal, true, true, $admin->profile);
        $this->agent = $this->agent->refreshed();

        $app = App::i();
        foreach ($this->relation->getSealRelationFields() as $field) {
            $field->expiryDate = match ($field->fieldName) {
                'agent.name' => new DateTime('-1 day', new DateTimeZone('UTC')),
                'agent.nomeCompleto' => new DateTime('+3 days', new DateTimeZone('UTC')),
                'agent.documento' => null,
                default => new DateTime('+30 days', new DateTimeZone('UTC')),
            };
            $app->em->persist($field);
        }
        $this->relation->updateComputedStatus();
        $app->em->persist($this->relation);
        $app->em->flush();
    }

    protected function countNotificationsForUser(\MapasCulturais\Entities\User $user): int
    {
        return count(App::i()->repo(Notification::class)->findBy(['user' => $user]));
    }

    protected function runExpirationJobOnce(): void
    {
        $this->processJobs(as_date: '2100-01-01 00:00', number_of_jobs: 1);
    }

    public function testJobSendsExpiredAndAboutToExpireNotifications(): void
    {
        $before = $this->countNotificationsForUser($this->owner);
        $this->runExpirationJobOnce();
        $after = $this->countNotificationsForUser($this->owner);

        $this->assertSame($before + 2, $after, 'Owner should receive one expired and one about-to-expire notification');
    }

    public function testJobDoesNotSendDuplicateNotifications(): void
    {
        $this->runExpirationJobOnce();
        $first = $this->countNotificationsForUser($this->owner);

        $this->runExpirationJobOnce();
        $second = $this->countNotificationsForUser($this->owner);

        $this->assertSame($first, $second, 'Running the job again should not send duplicate notifications');
    }

    public function testNotificationsAreNotSentToAdminOrManager(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $manager = $this->userDirector->createUser();
        $this->agent->createAgentRelation($manager->profile, 'admin', true);
        $this->processPCache();

        $adminBefore = $this->countNotificationsForUser($admin);
        $managerBefore = $this->countNotificationsForUser($manager);

        $this->runExpirationJobOnce();

        $this->assertSame($adminBefore, $this->countNotificationsForUser($admin));
        $this->assertSame($managerBefore, $this->countNotificationsForUser($manager));
    }

    public function testRenewalResetsNotificationFlags(): void
    {
        $this->runExpirationJobOnce();

        $app = App::i();
        $relation = $app->repo('AgentSealRelation')->find($this->relation->id);

        foreach ($relation->getSealRelationFields() as $field) {
            $this->assertTrue($field->notifiedExpire || $field->notifiedToExpire || $field->expiryDate === null);
        }

        foreach ($relation->getSealRelationFields() as $field) {
            $field->renew();
            $app->em->persist($field);
        }
        $app->em->flush();

        foreach ($relation->getSealRelationFields() as $field) {
            $this->assertFalse($field->notifiedExpire);
            $this->assertFalse($field->notifiedToExpire);
        }
    }

    public function testComputedStatusUpdatedAfterExpirationJob(): void
    {
        $this->runExpirationJobOnce();

        $app = App::i();
        $relation = $app->repo('AgentSealRelation')->find($this->relation->id);

        $this->assertSame('invalid', $relation->computedStatus);
    }

    public function testSensitiveSealNotificationIsGeneric(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $sensitiveSeal = $this->sealDirector->createSeal($admin->profile);
        $sensitiveSeal->validPeriod = 0;
        $sensitiveSeal->sensitive = true;
        $sensitiveSeal->name = 'Selo Sensível PCD';
        $sensitiveSeal->lockedFieldsConfig = [
            'agent.name' => ['hasExpiry' => true, 'periodValue' => 1, 'periodUnit' => 'year', 'isInvalidator' => false],
        ];
        $sensitiveSeal->save(true);

        $sensitiveOwner = $this->userDirector->createUser();
        $sensitiveAgent = $sensitiveOwner->profile;
        $sensitiveRelation = $sensitiveAgent->createSealRelation($sensitiveSeal, true, true, $admin->profile);

        foreach ($sensitiveRelation->getSealRelationFields() as $field) {
            $field->expiryDate = new DateTime('-1 day', new DateTimeZone('UTC'));
            App::i()->em->persist($field);
        }
        App::i()->em->flush();

        $this->runExpirationJobOnce();

        $notifications = App::i()->repo(Notification::class)->findBy(['user' => $sensitiveOwner]);
        $this->assertCount(1, $notifications);

        $message = $notifications[0]->message;
        $this->assertStringContainsString('Um selo sensível', $message);
        $this->assertStringNotContainsString('Selo Sensível PCD', $message);
        $this->assertStringNotContainsString('agent.name', $message);
    }
}
