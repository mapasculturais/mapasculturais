<?php
use \Slim\Log;

$config = include 'conf-base.php';

return array_merge($config,
    array(
        'base.url' => 'http://teste.mapasculturais.local',
        'site.url' => 'http://mapasculturais.local/',
        'app.log.translations' => false,
        'slim.log.level' => Log::DEBUG,
        'slim.log.enabled' => true,
        'slim.debug' => true,
        'auth.provider' => 'Test',
        'auth.config' => array(),

        'userIds' => array(
            'superAdmin' => 1,
//            'admin' => 2,
//            'staff' => 3,
//            'normal' => 4,

            'admin' => 85,
            'staff' => 67,
            'normal' => 61,
        )
    )
);
