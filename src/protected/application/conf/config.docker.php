<?php
$config = include 'conf-base.php';

return array_merge($config,
    [
        'app.siteName' => \MapasCulturais\i::__('Nome do site'),
        'app.siteDescription' => \MapasCulturais\i::__('Descrição do site'),

        /* configure e descomente as linhas abaixo para habilitar um tema personalizado */
        // 'namespaces' => array_merge( $config['namespaces'], ['Name\Space\Do\Tema' => '/caminho/absoluto/para/o/tema']),
        // 'themes.active' => 'Name\Space\Do\Tema',

	/* to setup Saas Subsite theme */
	//'namespaces' => array(
        //    'MapasCulturais\Themes' => THEMES_PATH,
        //    'TemplateV1' => THEMES_PATH . '/TemplateV1/',
        //    'Subsite' => THEMES_PATH . '/Subsite/'
        //),

        // development, staging, production
        'app.mode' => 'production',

        'doctrine.isDev' => false,
        'slim.debug' => true,
        'maps.includeGoogleLayers' => true,
        'auth.provider' => 'Fake',

        // Token da API de Cep
        // Adquirido ao fazer cadastro em http://www.cepaberto.com/
        // 'cep.token' => '[token]',

        /* Modelo de configuração para usar o id da cultura */
//        'auth.provider' => 'OpauthOpenId',
//        'auth.config' => [
//            'login_url' => '',
//            'logout_url' => '',
//            'salt' => '',
//            'timeout' => '24 hours'
//        ],

//Modelo de autenticação para Login Cidadão
//        'auth.provider' => 'OpauthLoginCidadao',
//        'auth.config' => array(
//        'client_id' => '',
//        'client_secret' => '',
//        'auth_endpoint' => 'https://[SUA-URL]/openid/connect/authorize',
//        'token_endpoint' => 'https://[SUA-URL]/openid/connect/token',
//        'user_info_endpoint' => 'https://[SUA-URL]/api/v1/person.json'
//        ),

        'doctrine.database' => [
            'dbname'    => 'mapas',
            'user'      => 'mapas',
            'host'      => 'db',
            'password'  => 'senhaMapas',
        ],
    ]
);
