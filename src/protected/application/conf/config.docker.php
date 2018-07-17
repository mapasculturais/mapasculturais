<?php
$config = include 'conf-base.php';

 $theme_namespace    = 'CulturaEnLinea'; // namespace do tema
    $theme_path         = THEMES_PATH . 'culturaenlinea';         // caminho para a pasta do tema

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
        
'namespaces' => array_merge( $config['namespaces'], [$theme_namespace => $theme_path, 'MecTeatros' => THEMES_PATH . 'teatros']),

//        'themes.active' => 'MapasCulturais\Themes\BaseV1',
        'themes.active' => $theme_namespace,        
        
'app.lcode' => 'es_ES',
        // development, staging, production
        'app.mode' => 'development',

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
