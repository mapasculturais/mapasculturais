<?php
use \Slim\Log;

$config = include 'conf-base.php';

return array_merge($config,
    array(
        'base.url' => 'http://teste.mapasculturais.local',
        'site.url' => 'http://localhost:8000/',
        'app.fakeAuthentication' => true,
        'app.log.translations' => false,
        'slim.log.level' => Log::DEBUG,
        'slim.log.enabled' => true,
        'slim.debug' => true,
    )
);
