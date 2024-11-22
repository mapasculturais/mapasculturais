<?php
use Symfony\Component\Cache\Adapter\ArrayAdapter;

$is_production = false;

return array(
    'themes.active' => '\MapasCulturais\Themes\Maranhao',
    'base.url' => 'http://localhost/',
    'site.url' => 'http://localhost/',
    'app.log.translations' => false,
    'slim.log.enabled' => false,
    'slim.debug' => false,
    'auth.provider' => 'Test',

    'auth.config' => array(
        'filename' => '/tmp/mapasculturais-tests-authenticated-user.id'
    ),

    'doctrine.isDev' => false,

    'db.host' => 'database',
    'db.port' => '5432',
    'db.dbname' => 'mapas',
    'db.user' => 'mapas',
    'db.password' => 'mapas',

    'userIds' => array(
        'superAdmin' => array(1,2),
        'admin' => array(3,4),
        'staff' => array(5,6),
        'normal' => array(7,8),
    ),

    // App settings
    'app.usePermissionsCache' => false,
    'app.cache' => new ArrayAdapter(),
    'app.mscache' => new ArrayAdapter(),
    // $this->app->mscache->setNamespace('MS');
    'app.rcache' => new ArrayAdapter(),
    'app.mode' => 'production',
    'app.offline' => false,
    'app.lcode' => 'pt_BR',
    'app.currency' => 'R$',
    'app.useAssetsUrlCache' => false,

    // Routes configuration
    'routes' => [
        'default' => [
            'id' => 'default',
            'patterns' => [
                'controller' => '/[^\/]+/',
                'action' => '/[^\/]+/',
                'id' => '/[^\/]+/'
            ]
        ]
    ],

    // Session configuration
    'slim.session.save_path' => '/tmp',

    // Asset manager configuration
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem([
        'publishPath' => '/tmp/assets/',
        'mergeScripts' => $is_production,
        'mergeStyles' => $is_production,
        'process.js' => !$is_production ? 'cp {IN} {OUT}' : 'terser {IN} --source-map --output {OUT}',
        'process.css' => !$is_production ? 'cp {IN} {OUT}' : 'uglifycss {IN} > {OUT}',
        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    ]),

    'app.log.query' => false,
    'app.log.hook' => [],
    'app.log.assets' => false,
    'app.verifiedSealsIds' => [],

    // Authentication
    'auth.provider' => 'Fake',
    'auth.config' => [],

    // Middleware configuration
    'middlewares' => [],

    // Monolog settings
    'monolog.handlers' => 'file:WARNING,error_log:DEBUG,telegram:CRITICAL',
    'monolog.processors' => [],

    // Plugin configurations
    'plugins' => [
        'ProjectName' => ['namespace' => 'PluginNamespace']
    ],
);
