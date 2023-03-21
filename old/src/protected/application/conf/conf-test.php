<?php
use \Slim\Log;

$config = include 'conf-base.php';
return array_merge($config,
    array(
        'base.url' => 'http://localhost:8888/',
        'site.url' => 'http://localhost:8888/',
        'app.log.translations' => false,
        'slim.log.level' => Log::DEBUG,
        'slim.log.enabled' => false,
        'app.mode' => 'production',
        'slim.debug' => false,
        'auth.provider' => 'Test',

        'auth.config' => array(
            'filename' => '/tmp/mapasculturais-tests-authenticated-user.id'
        ),
        
        'doctrine.isDev' => false,

        'doctrine.database' => array(
            'dbname'    => env('DB_NAME', 'mapasculturais_test'),
            'user'      => env('DB_USER', 'mapasculturais_test'),
            'password'  => env('DB_PASS', 'mapasculturais_test'),
            'host'      => env('DB_HOST', 'localhost'),

        ),

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

        'app.usePermissionsCache' => false,
        'app.cache' => new \Doctrine\Common\Cache\ArrayCache(),

        
        'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem([
            'publishPath' => BASE_PATH . 'assets/',
    
            'mergeScripts' => false,
            'mergeStyles' => false,
    
            'process.js' => 'cp {IN} {OUT}',
            'process.css' => 'cp {IN} {OUT}',
    
            'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
        ])
    )
);
