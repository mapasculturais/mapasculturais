<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = include 'conf-base.php';

// devel
return array_merge($config,
    array(
//        'namespaces' => array_merge( $config['namespaces'], array('SpCultura' => THEMES_PATH . 'SpCultura') ),
//        'themes.active' => 'SpCultura',
//        'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(array(
//            'publishPath' => BASE_PATH . 'pub/',
//
//            'mergeScripts' => false,
//            'mergeStyles' => true,
//
//            'process.js' => 'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /pub/{FILENAME}.map -b -p 7',
//            'process.css' => 'uglifycss {IN} > {OUT}',
//            'publishFolderCommand' => 'ln -s -f {IN} {PUBLISH_PATH}'
//
//        )),
//        'base.assetUrl' => 'http://localhost:8000/pub/',

        'base.url' => 'http://localhost:8000/',

        'app.mode' => 'development', // development, staging, production

        'doctrine.isDev' => false,
        'slim.debug' => true,
        'slim.middlewares' => array(
            new MapasCulturais\Middlewares\ErrorHandler(function(){ return 'mapasculturais.log'; }),

            new MapasCulturais\Middlewares\ExecutionTime(true, false)
        ),

        'app.geoDivisionsHierarchy' => array(
            'zona' => 'Zona',
            'subprefeitura' => 'Subprefeitura',
            'distrito' => 'Distrito'
        ),
        'maps.center' => array(-23.54894, -46.63882), // são paulo

        // logs
        'slim.log.writer' => new \MapasCulturais\Loggers\File(function(){ return 'mapasculturais.log'; }),
        'app.log.path' => '/tmp/',

        'slim.log.level' => \Slim\Log::DEBUG,
        'slim.log.enabled' => true,

        'app.log.hook' => false,
        'app.log.query' => false,
        'app.log.requestData' => false,
        'app.log.translations' => true,
        'app.log.apiCache' => false,

        // cache
        'app.useAssetsUrlCache' => false,

        'app.useApiCache' => true,
        'app.apiCache.lifetime' => 10 * 60,

        'app.useTranslationsCache' => true,

        'app.useRegisterCache' => true,
        'app.registerCache.lifeTime' => 10 * 60,

        'plugins.enabled' => array('em-cartaz', 'agenda-singles'),

        /*
        'auth.provider' => 'OpauthOpenId',
        'auth.config' => array(
            'login_url' => 'http://localhost:9000/',
            'logout_url' => 'http://localhost:9000/accounts/logout/',
            'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
            'timeout' => '24 hours'
        ),
        // */

        //*
        'auth.provider' => 'Fake',
        'auth.config' => array(),
        // */

    )
);
