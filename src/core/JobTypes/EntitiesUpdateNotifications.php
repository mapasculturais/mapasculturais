<?php
namespace MapasCulturais\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Notification;
use MapasCulturais\i;

/**
 * Tipo de job que notifica o usuário sobre agentes e espaços desatualizados
 *
 * Executado em segundo plano após o login, para não bloquear a requisição
 * de usuários donos de muitas entidades.
 *
 * @package MapasCulturais\JobTypes
 */
class EntitiesUpdateNotifications extends JobType
{
    /**
     * @const string SLUG Identificador único do tipo de job
     */
    const SLUG = "entitiesUpdateNotifications";

    /**
     * @const int BATCH_SIZE Quantidade de entidades processadas antes de limpar o entity manager
     */
    const BATCH_SIZE = 20;

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "entitiesUpdateNotifications:{$data['userId']}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job)
    {
        $app = App::i();

        $days = (int) $app->config['notifications.entities.update'];
        if (!isset($app->modules['Notifications']) || $days <= 0) {
            return true;
        }

        $user_id = $job->userId;

        $agent_ids = $app->em->createQuery("
            SELECT e.id
            FROM MapasCulturais\\Entities\\Agent e
            WHERE e.user = :user AND e.status > 0
            ORDER BY e.createTimestamp ASC
        ")->setParameter('user', $user_id)->getSingleColumnResult();

        $space_ids = $app->em->createQuery("
            SELECT e.id
            FROM MapasCulturais\\Entities\\Space e
            JOIN e.owner a
            WHERE a.user = :user AND e.status > 0
            ORDER BY e.name, e.createTimestamp ASC
        ")->setParameter('user', $user_id)->getSingleColumnResult();

        $messages = [
            'Agent' => i::__("O agente <b>%s</b> não é atualizado desde de <b>%s</b>, atualize as informações se necessário. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>'"),
            'Space' => i::__("O Espaço <b>%s</b> não é atualizado desde de <b>%s</b>, atualize as informações se necessário. <a class='btn btn-small btn-primary' href='%s' rel='noopener noreferrer'>editar</a>"),
        ];

        $now = new \DateTime;
        $count = 0;

        foreach (['Agent' => $agent_ids, 'Space' => $space_ids] as $class => $ids) {
            foreach ($ids as $id) {
                $entity = $app->repo($class)->find($id);
                if (!$entity || $entity->sentNotification) {
                    continue;
                }

                $last_update = $entity->updateTimestamp ?: $entity->createTimestamp;
                if (date_diff($last_update, $now)->format('%a') < $days) {
                    continue;
                }

                try {
                    $notification = new Notification;
                    $notification->user = $app->repo('User')->find($user_id);
                    $notification->message = sprintf($messages[$class], $entity->name, $last_update->format("d/m/Y"), $entity->editUrl);
                    $notification->save();

                    // use the notification id to use it later on entity update
                    $entity->sentNotification = $notification->id;
                    $entity->save();
                } catch (\Exception $e) {
                    $app->log->error("ERRO AO NOTIFICAR ENTIDADE DESATUALIZADA ({$class}:{$id}): {$e->getMessage()}");
                    continue;
                }

                if (++$count % self::BATCH_SIZE == 0) {
                    $this->flushAndClear($user_id);
                }
            }
        }

        $this->flushAndClear($user_id);

        return true;
    }

    /**
     * Grava o lote atual e libera a memória do entity manager
     *
     * @param int $user_id Id do usuário dono das entidades
     */
    protected function flushAndClear(int $user_id)
    {
        $app = App::i();

        $app->em->flush();
        $app->em->clear();

        // after clear the authenticated user is detached, so reload it
        $app->auth->authenticatedUser = $app->repo('User')->find($user_id);
    }
}
