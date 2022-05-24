<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

require __DIR__ . '/../application/bootstrap.php';

if($app->config['app.log.jobs']){
    $app->log->debug('EXECUTE JOB ' . date('Y-m-d H:i:s'));
}

$app = MapasCulturais\App::i();

$app->executeJob();
$app->em->getConnection()->close();