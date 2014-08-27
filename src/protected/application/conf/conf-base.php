<?php
date_default_timezone_set('America/Sao_Paulo');

// creating base url
$prot_part = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://';
//added @ for HTTP_HOST undefined in Tests
$host_part = @$_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
if(substr($host_part,-1) !== '/') $host_part .= '/';
$base_url = $prot_part . $host_part;

return array(
    // sempre colocar a barra no final da url
    'base.url' => $base_url,
    
    'vectorLayersPath' => 'geojson',

    // development, staging, production
    'app.mode' => 'production',
    'app.lcode' => 'pt-br',

    'app.dbUpdatesDisabled' => false,
    'app.defaultApiOutput' => 'json',

    'app.siteName' => 'Mapas Culturais',
    'app.siteDescription' => 'O Mapas Culturais é uma plataforma livre para mapeamento cultural.',
    
    // 'app.projectRegistrationAgentRelationGroupName' => "Inscrições",


    /* ==================== LOG ================== */
    // write log messages to a custom output (the class must implement the method "public write(mixed $message, int $level)")
    //'slim.log.writer' => new \Custom\Log\Writer(),

    'slim.log.level' => \Slim\Log::NOTICE,
    'slim.log.enabled' => true,

    'app.log.path' => realpath(BASE_PATH . '..') . '/logs/',

    'app.queryLogger' => new MapasCulturais\Loggers\DoctrineSQL\SlimLog(),
    'app.log.query' => false,

    'app.log.hook' => false,
    'app.log.requestData' => false,
    'app.log.translations' => false,
    'app.log.apiCache' => false,
    'app.log.apiDql' => false,

    /* ==================== CACHE ================== */
    'app.cache' => new \Doctrine\Common\Cache\ApcCache(),
    
    'app.useEventsCache' => true,
    'app.eventsCache.lifetime' => 600,

    'app.useApiCache' => true,
    'app.apiCache.lifetime' => 600,

    'app.useRegisterCache' => true,
    'app.registerCache.lifeTime' => 600,

    'app.useTranslationsCache' => true,

    /*
    'storage.driver' => '\MapasCulturais\Storage\FileSystem',

    'storage.config' => array(
        'dir' => realpath(__DIR__ . '/../themes/active/files/'),
        'baseUrl' => '/public/files/'
    ),
    */

    /* ================ SLIM ============== */
    'slim.debug' => false,
    'slim.middlewares' => array(
        //new \MapasCulturais\Middlewares\ExecutionTime(true, false)
    ),

    /* ================ DOCTRINE =============== */

    // basically this tell to doctrine orm to use or not use a persistent cache if available
    // see: https://github.com/doctrine/doctrine2/blob/2.3/lib/Doctrine/ORM/Tools/Setup.php#LC160
    'doctrine.isDev' => false,

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

    'plugins.enabled' => array(

    ),

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
            'sobre'     => array('site',    'page', array('sobre')),
            'como-usar' => array('site',    'page', array('como-usar'))
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
