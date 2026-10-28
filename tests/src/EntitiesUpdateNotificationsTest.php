<?php

namespace Tests;

use Tests\Abstract\TestCase;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

class EntitiesUpdateNotificationsTest extends TestCase
{
    use UserDirector,
        SpaceDirector;

    function testLoginEnqueuesJobAndJobNotifiesOutdatedEntities()
    {
        $app = $this->app;

        $user = $this->userDirector->createUser();
        $this->login($user);

        $outdated_space = $this->spaceDirector->createSpace($user->profile);
        $updated_space = $this->spaceDirector->createSpace($user->profile);

        $days = (int) $app->config['notifications.entities.update'];
        $this->assertGreaterThan(0, $days, 'Garantindo que os avisos de entidade desatualizada estão ligados por padrão');

        $old_date = (new \DateTime)->modify('-' . ($days + 10) . ' days')->format('Y-m-d H:i:s');
        $app->conn->executeQuery("UPDATE space SET update_timestamp = '{$old_date}', create_timestamp = '{$old_date}' WHERE id = {$outdated_space->id}");
        $app->conn->executeQuery("UPDATE agent SET status = 1, update_timestamp = '{$old_date}', create_timestamp = '{$old_date}' WHERE id = {$user->profile->id}");
        $app->em->clear();

        $user = $user->refreshed();
        $this->login($user);

        $notifications_before = $this->countNotifications($user->id);

        $user->getEntitiesNotifications($app);

        $this->assertEquals($notifications_before, $this->countNotifications($user->id),
            'Garantindo que o login não cria os avisos de entidade desatualizada na própria requisição');

        $this->processJobsSafely();

        $this->assertEquals($notifications_before + 2, $this->countNotifications($user->id),
            'Garantindo que o job cria um aviso para o espaço e um para o agente desatualizados');

        $this->assertNotEmpty($app->repo('Space')->find($outdated_space->id)->sentNotification,
            'Garantindo que o espaço desatualizado fica marcado como avisado');

        $this->assertEmpty($app->repo('Space')->find($updated_space->id)->sentNotification,
            'Garantindo que o espaço atualizado não recebe aviso');

        $user = $user->refreshed();
        $this->login($user);
        $user->getEntitiesNotifications($app);
        $this->processJobsSafely();

        $this->assertEquals($notifications_before + 2, $this->countNotifications($user->id),
            'Garantindo que um novo login não duplica os avisos já enviados');
    }

    private function countNotifications(int $user_id): int
    {
        return (int) $this->app->conn->fetchScalar("SELECT count(*) FROM notification WHERE user_id = {$user_id}");
    }

    /**
     * executeJob() faz (int) no id md5; ids que começam com [a-f] viram 0 e quebram
     * o while ($app->executeJob()) do processJobs padrão.
     */
    private function processJobsSafely(int $max_jobs = 10): void
    {
        $app = $this->app;
        $current_loggedin_user = $app->user;

        for ($i = 0; $i < $max_jobs; $i++) {
            if ($app->executeJob('2100-01-01 00:00') === false) {
                break;
            }
        }

        $this->login($current_loggedin_user->refreshed());
    }
}
