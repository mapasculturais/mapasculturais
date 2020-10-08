<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');
unset($_ENV['LOG_HOOK']);
require __DIR__ . '/../application/bootstrap.php';

$app = MapasCulturais\App::i();

if($app->config['app.log.pcache']){
    $app->log->debug('RECREATE PENDING PCACHE');
}

$app->recreatePermissionsCache();
$app->em->getConnection()->close();