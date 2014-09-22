<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = include 'conf-base.php';

// devel
return array_merge($config,
    array(
        'base.url' => 'http://localhost:8000/',
        // development, staging, production
        'app.mode' => 'development',

        'doctrine.isDev' => true,

        // 'slim.middlewares' => array( new \MapasCulturais\Middlewares\ExecutionTime(true, false) ),

        // logs
        'slim.log.level' => \Slim\Log::DEBUG,
        'slim.log.enabled' => true,

        'app.log.hook' => true,
        'app.log.query' => false,
        'app.log.requestData' => false,
        'app.log.translations' => false,
        'app.log.apiCache' => true,

        // cache
        'app.useApiCache' => false,
        'app.apiCache.lifetime' => 10 * 60,

        'app.useTranslationsCache' => true,

        'app.useRegisterCache' => true,
        'app.registerCache.lifeTime' => 10 * 60,

        'app.cache' => new \Doctrine\Common\Cache\ArrayCache(),

        
        'auth.provider' => 'Fake',
        'auth.config' => array(),


        'plugins.enabled' => array(
            'agenda-singles',
            'endereco'
        )
    )
);
