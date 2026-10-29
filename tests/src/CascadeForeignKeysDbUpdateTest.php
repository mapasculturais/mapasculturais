<?php

namespace Tests;

use MapasCulturais\Connection;
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

    private function connection(): Connection
    {
        return $this->app->em->getConnection();
    }
}
