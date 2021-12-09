<?php
$time_start = microtime(true);
$save_log = isset($argv[1]) && $argv[1];
if($save_log)
    ob_start();

require __DIR__ . '/application/bootstrap.php';

if($save_log)
    $log = ob_get_clean();

$time_end = microtime(true);

$execution_time = number_format($time_end - $time_start, 4);


$exec_time = "
=================================================
send notifications executed in {$execution_time} seconds.
=================================================\n";

if($save_log && $log){
    $log_path = MapasCulturais\App::i()->config['app.log.path'];
    $log_filename = 'send-notifications' . date('Y.m.d-H.i.s') . '.log';
    file_put_contents( $log_path . $log_filename, $exec_time . $log);
}else{
    echo $exec_time;
}

$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

$dql = "
    SELECT
        u
    FROM
        MapasCulturais\Entities\User u
    WHERE
        u.status = '1'";

$query = $em->createQuery($dql);

$userList = $query->getResult();

foreach($userList as $reg) {
    $user = $app->repo('User')->find($reg->id);
    echo "Gerando e-mails para o usuário " . $user->email ."\n";
    if($app->config['notifications.user.access'] > 0) {
      $now = new \DateTime;
      $interval = date_diff($user->lastLoginTimestamp, $now);
      if($interval->format('%a') >= $app->config['notifications.user.access']) {
        $dataValue = [
            'name'                 => $user->profile->name,
            'last_login_timestamp' => $user->lastLoginTimestamp->format('d/m/Y')
        ];

        $message = $app->renderMailerTemplate('last_login',$dataValue);

        // message to user about last access system
        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => $message['title'],
            'body' => $message['body']
        ]);
      }
    }

    if($app->config['notifications.entities.update'] > 0) {
        $now = new \DateTime;
        foreach($user->agents as $agent) {
            $lastUpdateDate = $agent->updateTimestamp ? $agent->updateTimestamp: $agent->createTimestamp;
            $interval = date_diff($lastUpdateDate, $now);

            $dataValue = [
                'name'          => $user->profile->name,
                'entityType'    => $agent->entityTypeLabel,
                'entityName'    => $agent->name,
                'url'           => $agent->singleUrl,
                'lastUpdateTimestamp'=> $lastUpdateDate->format("d/m/Y")
            ];

            if($agent->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
                $message = $app->renderMailerTemplate('update_required',$dataValue);
                // message to user about old agent registrations
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $user->email,
                    'subject' => $message['title'],
                    'body' => $message['body']
                ]);
            }

            if(in_array('notifications.seal.toExpire',$app->config) && $app->config['notifications.seal.toExpire'] > 0) {
                foreach($agent->sealRelations as $relation) {
                    $dataValue['sealName'] = $relation->seal->name;
                    if(isset($relation->validateDate) && $relation->validateDate->date) {
                        $diff = ($relation->validateDate->format("U") - $now->format("U"))/86400;
                        if($diff <= 0.00) {
                            $message = $app->renderMailerTemplate('seal_expired',$dataValue);
                        } elseif($diff <= $app->config['notifications.seal.toExpire']) {
                            $diff = is_int($diff)? $diff: round($diff);
                            $diff = $diff == 0? $diff = 1: $diff;
                            $dataValue['daysToExpire'] = $diff;
                            $message = $app->renderMailerTemplate('seal_toexpire',$dataValue);
                        }
                    }

                    if(!empty($message)) {
                        // message to user about old agent registrations
                        $app->createAndSendMailMessage([
                            'from' => $app->config['mailer.from'],
                            'to' => $user->email,
                            'subject' => $message['title'],
                            'body' => $message['body']
                        ]);
                    }
                }
            }
        }

        foreach($user->projects as $project) {
          $lastUpdateDate = $project->updateTimestamp ? $project->updateTimestamp: $project->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($project->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            $dataValue = [
                'name'          => $user->profile->name,
                'entityType'    => $project->entityTypeLabel,
                'entityName'    => $project->name,
                'url'           => $project->singleUrl,
                'lastUpdateTimestamp'=> $lastUpdateDate->format("d/m/Y")
            ];

            $message = $app->renderMailerTemplate('update_required',$dataValue);
            // message to user about old project registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
          }
        }

        foreach($user->events as $event) {
          $lastUpdateDate = $event->updateTimestamp ? $event->updateTimestamp: $event->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($interval->format('%a') >= $app->config['notifications.entities.update']) {
            $dataValue = [
                'name'          => $user->profile->name,
                'entityType'    => $event->entityTypeLabel,
                'entityName'    => $event->name,
                'url'           => $event->singleUrl,
                'lastUpdateTimestamp'=> $lastUpdateDate->format("d/m/Y")
            ];

            $message = $app->renderMailerTemplate('update_required',$dataValue);
            // message to user about old event registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
          }
        }

        foreach($user->spaces as $space) {
            $lastUpdateDate = $space->updateTimestamp ? $space->updateTimestamp: $space->createTimestamp;
            $interval = date_diff($lastUpdateDate, $now);
            if($space->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
                $dataValue = [
                    'name'          => $user->profile->name,
                    'entityType'    => $space->entityTypeLabel,
                    'entityName'    => $space->name,
                    'url'           => $space->singleUrl,
                    'lastUpdateTimestamp'=> $lastUpdateDate->format("d/m/Y")
                ];

                $message = $app->renderMailerTemplate('update_required',$dataValue);
                // message to user about old space registrations
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $user->email,
                    'subject' => $message['title'],
                    'body' => $message['body']
                ]);
            }
            if(in_array('notifications.seal.toExpire',$app->config) && $app->config['notifications.seal.toExpire'] > 0) {
                foreach($space->sealRelations as $relation) {
                    $dataValue['sealName'] = $relation->seal->name;
                    if(isset($relation->validateDate) && $relation->validateDate->date) {
                        $diff = ($relation->validateDate->format("U") - $now->format("U"))/86400;
                        if($diff <= 0.00) {
                            $message = $app->renderMailerTemplate('seal_expired',$dataValue);
                        } elseif($diff <= $app->config['notifications.seal.toExpire']) {
                            $diff = is_int($diff)? $diff: round($diff);
                            $diff = $diff == 0? $diff = 1: $diff;
                            $dataValue['daysToExpire'] = $diff;
                            $message = $app->renderMailerTemplate('seal_toexpire',$dataValue);
                        }
                    }
                    if(!empty($message)) {
                        // message to user about old agent registrations
                        $app->createAndSendMailMessage([
                            'from' => $app->config['mailer.from'],
                            'to' => $user->email,
                            'subject' => $message['title'],
                            'body' => $message['body']
                        ]);
                    }
                }
            }

        }

        /* @TODO avaliar necessidade de notificar os registros de seloss
        foreach($user->seals as $seal) {
          $lastUpdateDate = $seal->updateTimestamp ? $seal->updateTimestamp: $seal->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($seal->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            $dataValue = [
                'name'          => $user->profile->name,
                'entityType'    => $seal->entityTypeLabel,
                'entityName'    => $seal->name,
                'url'           => $seal->singleUrl,
                'lastUpdateTimestamp'=> $lastUpdateDate->format("d/m/Y")
            ];

            $message = $app->renderMailerTemplate('update_required',$dataValue);

            // message to user about old seal registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O selo <b>" . $seal->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
      }*/
    }
    $app->auth->logout();
}
