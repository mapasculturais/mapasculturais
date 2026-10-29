<?php

namespace Tests;

use Doctrine\DBAL\ArrayParameterType;
use MapasCulturais\Connection;
use MapasCulturais\Entities\Agent;
use MapasCulturais\JobTypes\EntitiesUpdateNotifications;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;

/**
 * Regressão das chaves estrangeiras de cascata de job e permission_cache_pending:
 * as entidades declaravam onDelete=cascade, mas a FK nunca chegou a ser criada no banco.
 */
class CascadeForeignKeysDbUpdateTest extends TestCase
{
    use UserDirector;

    public function testDestroyingUserRemovesItsJobs(): void
    {
        $user = $this->userDirector->createUser();
        $user_id = $user->id;
        $job_id = $this->app->enqueueJob(EntitiesUpdateNotifications::SLUG, ['userId' => $user_id], user: $user)->id;

        $this->assertSame(1, $this->countJobs($job_id), 'o job precisa existir antes da exclusão do usuário');

        $this->destroyUser($user_id);

        $this->assertSame(0, $this->countJobs($job_id), 'destruir o usuário tem que levar o job dele junto');
    }

    public function testDestroyingUserRemovesItsPendingPermissionCache(): void
    {
        $user = $this->userDirector->createUser();
        $user_id = $user->id;

        $this->connection()->executeStatement(
            "INSERT INTO permission_cache_pending (id, object_type, object_id, usr_id)
             VALUES (nextval('agent_id_seq'::regclass), :object_type, :object_id, :usr_id)",
            [
                'object_type' => Agent::class,
                'object_id' => $user->profile->id,
                'usr_id' => $user_id,
            ],
        );

        $this->assertSame(1, $this->countPendingPermissionCache($user_id), 'a pendência precisa existir antes da exclusão');

        $this->destroyUser($user_id);

        $this->assertSame(0, $this->countPendingPermissionCache($user_id), 'destruir o usuário tem que levar a pendência de pcache junto');
    }

    public function testTheThreeForeignKeysCascadeOnDelete(): void
    {
        $expected = ['fk_job_usr', 'fk_job_subsite', 'fk_permission_cache_pending_usr'];

        $found = $this->connection()->fetchFirstColumn(
            "SELECT conname FROM pg_constraint WHERE contype = 'f' AND confdeltype = 'c' AND conname IN (:names)",
            ['names' => $expected],
            ['names' => ArrayParameterType::STRING],
        );

        $this->assertEqualsCanonicalizing($expected, $found, 'as três chaves estrangeiras têm que existir com ON DELETE CASCADE');
    }

    private function destroyUser(int $user_id): void
    {
        $this->app->disableAccessControl();
        $this->app->repo('User')->find($user_id)->destroy(true);
        $this->app->enableAccessControl();
        $this->app->em->clear();
    }

    private function countJobs(string $job_id): int
    {
        return (int) $this->connection()->fetchOne('SELECT count(*) FROM job WHERE id = :id', ['id' => $job_id]);
    }

    private function countPendingPermissionCache(int $user_id): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT count(*) FROM permission_cache_pending WHERE usr_id = :id',
            ['id' => $user_id],
        );
    }

    private function connection(): Connection
    {
        return $this->app->em->getConnection();
    }
}
