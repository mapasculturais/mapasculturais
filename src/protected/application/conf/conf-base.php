<?php
use \Slim\Log;

date_default_timezone_set('America/Sao_Paulo');

return array(
    // sempre colocar a barra no final da url
    'base.url' => 'http://'.(array_key_exists('SERVER_NAME' , $_SERVER) ? ($_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']) ) : 'mapasculturais.local') . '/',

    'vectorLayersPath' => 'geojson',

    // development, staging, production
    'app.mode' => 'staging',

    'app.lcode' => 'pt-br',

    'app.useTranslationsCache' => false,

    'app.cache' => new \Doctrine\Common\Cache\ApcCache(),

    'app.defaultApiOutput' => 'json',

    'app.registerCache.enabled' => true,

    'app.registerCache.lifeTime' => 5 * 60,

    'app.log.hook' => false,

    'app.log.query' => true,

    'app.log.requestData' => false,

    'app.log.translations' => true,

    'app.log.apiCache' => false,

    'app.debugbar' => false,

    'app.dbUpdatesDisabled' => false,

    'app.useApiCache' => true,

    'app.apiCache.lifetime' => 10 * 60,

    'app.queryLogger' => new MapasCulturais\Loggers\DoctrineSQL\SlimLog(),

//    'app.projectRegistrationAgentRelationGroupName' => "Inscrições",


    //'app.js.minify' => true,

    /*
    'storage.driver' => '\MapasCulturais\Storage\FileSystem',

    'storage.config' => array(
        'dir' => realpath(__DIR__ . '/../themes/active/files/'),
        'baseUrl' => '/public/files/'
    ),
    */

    // (Log::FATAL, Log::ERROR, Log::WARN, Log::INFO, Log::DEBUG)
    'slim.log.level' => Log::DEBUG,

    'slim.log.enabled' => true,

    'slim.debug' => true,

    'slim.middlewares' => array(
        //new \MapasCulturais\Middlewares\ExecutionTime(true, false)
    ),

    // write log messages to a custom output (the class must implement the method "public write(mixed $message, int $level)")
    //'slim.log.writer' => new \MapasCulturais\Loggers\Slim\DebugBar(),

    // basically this tell to doctrine orm to use or not use a persistent cache if available
    // see: https://github.com/doctrine/doctrine2/blob/2.3/lib/Doctrine/ORM/Tools/Setup.php#LC160
    'doctrine.isDev' => true,

    'doctrine.database' => array(
        'dbname'    => 'mapasculturais',
        'user'      => 'mapasculturais',
        'password'  => 'mapasculturais',
        'host'      => 'localhost',
    ),

    // if authprovider namespace is outside MapasCulturais\AuthProvider set the full namespace with the initial slash ex: \Full\Name\Space\AuthProvider
    //*
    'auth.provider' => 'OpauthOpenId',
    'auth.config' => array(
        'login_url' => 'http://id.mapasculturais.hacklab.com.br',
        'logout_url' => 'http://id.mapasculturais.hacklab.com.br/accounts/logout/',
        'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
        'timeout' => '24 hours'
    ),
    // */

    /*
    'auth.provider' => 'Fake',
    'auth.config' => array(),
    // */

    //
    'routes' => array(
        'default_controller_id' => 'site',
        'default_action_name' => 'index',
        'shortcuts' => array(
            // exemplos de shortcut adicionando parametros
            // 'james-bond'                => array('agent', 'single', array('id' => '007')),
            // 'agente/007'                => array('agent', 'single', array('id' => '007')),
            // 'teste/de/shortcut/longo'   => array('agent', 'single', array('id' => 'shortcut longo')),

            'evento'    => array('event',   'single'),
            'usuario'   => array('user',    'single'),
            'agente'    => array('agent',   'single'),
            'espaco'    => array('space',   'single'),
            'projeto'   => array('project', 'single'),
            'sair'      => array('auth',    'logout'),
            'busca'     => array('site',    'search'),
        ),
        'controllers' => array(
            'painel'         => 'panel',
            'autenticacao'   => 'auth',
            'site'           => 'site',
            'eventos'        => 'event',
            'agentes'        => 'agent',
            'espacos'        => 'space',
            'arquivos'       => 'file',
            'projetos'       => 'project'
        ),
        'actions' => array(
            'lista'         => 'list',
            'apaga'         => 'delete',
            'edita'         => 'edit',
            'espacos'       => 'spaces',
            'agentes'       => 'agents',
            'eventos'       => 'events',
            'projetos'      => 'projects',
            'contratos'     => 'contracts'
        ),

        'readableNames' => array(
            //controllers
                'panel'     => 'Painel',
                'auth'      => 'Autenticação',
                'site'      => 'Site',
                'event'     => 'Evento',    'events'    => 'Eventos',
                'agent'     => 'Agente',    'agents'    => 'Agentes',
                'space'     => 'Espaço',    'spaces'    => 'Espaços',
                'project'   => 'Projeto',   'projects'  => 'Projetos',
                'contract'  => 'Contrato',  'contracts' => 'Contratos',
                'file'      => 'Arquivo',   'files'     => 'Arquivos',
            //actions
                'list'      => 'Listando',
                'index'     => 'Índice',
                'delete'    => 'Apagando',
                'edit'      => 'Editando',
                'create'    => 'Criando novo',
                'search'    => 'Busca'
        )
    )
);
