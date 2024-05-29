<?php

use MapasCulturais\App;


$config = require __DIR__ . "/../../src/conf/config.php";

return array_merge($config,
    array(
        'themes.active' => '\MapasCulturais\Themes\BaseV2',
        'base.url' => 'http://localhost:8888/',
        'site.url' => 'http://localhost:8888/',
        'app.log.translations' => false,
        'slim.log.level' => 3,
        'slim.log.enabled' => false,
//        'app.log.query' => true,
        'slim.debug' => false,
        'auth.provider' => 'Test',

        'auth.config' => array(
            'filename' => '/tmp/mapasculturais-tests-authenticated-user.id'
        ),

//        'app.log.query' => true,
//        'app.log.apiDql' => true,

        
        'doctrine.isDev' => false,

        'userIds' => array(
            'superAdmin' => array(1,2),
//            'admin' => 2,
//            'staff' => 3,
//            'normal' => 4,

            'admin' => array(3,4),
            'staff' => array(5,6),
            'normal' => array(7,8),
        ),

        // disable cache
        'db.host' => 'db',

        'app.usePermissionsCache' => false,
        'app.chace' => new \Symfony\Component\Cache\Adapter\ArrayAdapter()
//        'app.cache' => function_exists('apc_store') ? new \Doctrine\Common\Cache\ApcCache() : new \Doctrine\Common\Cache\ArrayCache(),
    )
);
