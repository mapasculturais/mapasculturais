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
        // message to user about last access system
        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => "Acesse Mapas Culturais",
            'body' => "Seu último acesso foi em <b>" . $user->lastLoginTimestamp->format('d/m/Y') . "</b>, atualize suas informações se necessário."
        ]);
      }
    }

    if($app->config['notifications.entities.update'] > 0) {
        $now = new \DateTime;
        foreach($user->agents as $agent) {
          $lastUpdateDate = $agent->updateTimestamp ? $agent->updateTimestamp: $agent->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($agent->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            // message to user about old agent registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O agente <b>" . $agent->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
        }

        foreach($user->projects as $project) {
          $lastUpdateDate = $project->updateTimestamp ? $project->updateTimestamp: $project->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($project->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            // message to user about old project registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O projeto <b>" . $project->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
        }

        foreach($user->events as $event) {
          $lastUpdateDate = $event->updateTimestamp ? $event->updateTimestamp: $event->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($interval->format('%a') >= $app->config['notifications.entities.update']) {
            // message to user about old event registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O Evento <b>" . $event->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
        }

        foreach($user->spaces as $space) {
          $lastUpdateDate = $space->updateTimestamp ? $space->updateTimestamp: $space->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($space->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            // message to user about old space registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O Espaço <b>" . $space->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
        }

        foreach($user->seals as $seal) {
          $lastUpdateDate = $seal->updateTimestamp ? $seal->updateTimestamp: $seal->createTimestamp;
          $interval = date_diff($lastUpdateDate, $now);
          if($seal->status > 0 && $interval->format('%a') >= $app->config['notifications.entities.update']) {
            // message to user about old seal registrations
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $user->email,
                'subject' => "Acesse Mapas Culturais",
                'body' => "O selo <b>" . $seal->name . "</b> não é atualizado desde de <b>" . $lastUpdateDate->format("d/m/Y") . "</b>, atualize as informações se necessário."
            ]);
          }
      }
    }
    $app->auth->logout();
}
