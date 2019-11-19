<?php
$config = include 'conf-base.php';

return array_merge($config,
    [
        'app.siteName' => \MapasCulturais\i::__('Nome do site'),
        'app.siteDescription' => \MapasCulturais\i::__('Descrição do site'),

        /* configure e descomente as linhas abaixo para habilitar um tema personalizado */
        // 'namespaces' => array_merge( $config['namespaces'], ['Name\Space\Do\Tema' => '/caminho/absoluto/para/o/tema']),
        // 'themes.active' => 'Name\Space\Do\Tema',

        'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem([
            'publishPath' => BASE_PATH . 'assets/',

            'mergeScripts' => true,
            'mergeStyles' => true,

            'process.js' => 'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /assets/{FILENAME}.map -b -p ' . substr_count(BASE_PATH, '/'),
            'process.css' => 'uglifycss {IN} > {OUT} ',
            'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
        ]),

        // development, staging, production
        'app.mode' => 'development',

        'doctrine.isDev' => false,
        'slim.debug' => false,
        'maps.includeGoogleLayers' => true,

        'app.geoDivisionsHierarchy' => [
            'pais'              => ['name' => \MapasCulturais\i::__('País'),            'showLayer' => true],
            'regiao'            => ['name' => \MapasCulturais\i::__('Região'),          'showLayer' => true],
            'estado'            => ['name' => \MapasCulturais\i::__('Estado'),          'showLayer' => true],
            'mesorregiao'       => ['name' => \MapasCulturais\i::__('Mesorregião'),     'showLayer' => true],
            'microrregiao'      => ['name' => \MapasCulturais\i::__('Microrregião'),    'showLayer' => true],
            'municipio'         => ['name' => \MapasCulturais\i::__('Município'),       'showLayer' => true],
            'zona'              => ['name' => \MapasCulturais\i::__('Zona'),            'showLayer' => true],
            'subprefeitura'     => ['name' => \MapasCulturais\i::__('Subprefeitura'),   'showLayer' => true],
            'distrito'          => ['name' => \MapasCulturais\i::__('Distrito'),        'showLayer' => true],
            'setor_censitario'  => ['name' => \MapasCulturais\i::__('Setor Censitario'),'showLayer' => false]
        ],
        // latitude, longitude
        'maps.center' => [-13.987376214146455, -54.38232421875],

        // zoom do mapa
        'maps.zoom.default' => 5,

        'plugins.enabled' => array('agenda-singles', 'endereco'),

        'auth.provider' => 'Fake',

        /* Modelo de configuração para usar o id da cultura */
//        'auth.provider' => 'OpauthOpenId',
//        'auth.config' => [
//            'login_url' => '',
//            'logout_url' => '',
//            'salt' => '',
//            'timeout' => '24 hours'
//        ],

        'doctrine.database' => [
            'dbname'    => 'mapas',
            'user'      => 'vagrant',
            'host'      => '',
        ],
    ]
);
