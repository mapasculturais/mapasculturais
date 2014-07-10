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
        
        // 'slim.middlewares' => array( new \MapasCulturais\Middlewares\ExecutionTime(true, false) ),
        
        // logs
        'app.log.hook' => false,
        'app.log.query' => false,
        'app.log.requestData' => false,
        'app.log.translations' => false,
        'app.log.apiCache' => true,
        
        // cache
        'app.useApiCache' => true,
        'app.apiCache.lifetime' => 10 * 60,
        
        'app.useTranslationsCache' => true,
        
        'app.useRegisterCache' => true,
        'app.registerCache.lifeTime' => 10 * 60,
        
        
        /*
        'auth.provider' => 'Fake',
        'auth.config' => array(),
        // */

    )
);
