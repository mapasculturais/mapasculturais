<?php

$config = include 'conf-base.php';

return array_merge($config,
    array(
        'base.url' => 'http://teste.mapasculturais.local',
        'app.fakeAuthentication' => true,
        'storage.driver' => '\MapasCulturais\Storage\FileSystem',
        'storage.config' => array(
            'dir' => realpath('/tmp/'),
            'baseUrl' => '/files/'
        ),

        'slim.log.level' => Log::DEBUG,
        'slim.log.enabled' => true,
        'slim.debug' => true,
        'doctrine.isDev' => true,
        'doctrine.database' => array(
            'dbname'    => 'mapasculturais',
            'user'      => 'mapasculturais',
            'password'  => 'mapasculturais',
            'host'      => 'localhost',
        ),
    )
);
