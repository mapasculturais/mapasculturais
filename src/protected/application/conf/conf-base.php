<?php
date_default_timezone_set('America/Sao_Paulo');

if(!isset($asset_dir)){
    $asset_dir = 'assets/';
}


// creating base url
$prot_part = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://';
//added @ for HTTP_HOST undefined in Tests
$host_part = @$_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
if(substr($host_part,-1) !== '/') $host_part .= '/';
$base_url = $prot_part . $host_part;

$num_folders = count(explode('/',BASE_PATH . 'public'));

return array(
    // theme namespaces
    'namespaces' => array(
        'MapasCulturais\Themes' => THEMES_PATH
    ),

    // sempre colocar a barra no final da url
    'base.url' => $base_url,
    'base.assetUrl' => $base_url . $asset_dir,

    'vectorLayersPath' => 'geojson',

    // development, staging, production
    'app.mode' => 'production',
    'app.lcode' => 'pt-br',

    'app.dbUpdatesDisabled' => false,
    'app.defaultApiOutput' => 'json',

    'app.siteName' => 'Mapas Culturais',
    'app.siteDescription' => 'O Mapas Culturais é uma plataforma livre para mapeamento cultural.',

    'api.accessControlAllowOrigin' => '*',


    'themes.active' => 'MapasCulturais\Themes\BaseV1',
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(array(
        'publishPath' => BASE_PATH . $asset_dir,

        'mergeScripts' => false,
        'mergeStyles' => false,

        'process.js' => 'cp {IN} {OUT}', //'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /pub/{FILENAME}.map -b -p 7',
        'process.css' => 'cp {IN} {OUT}', //'uglifycss {IN} > {OUT}',
        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    )),

//    'maps.center' => array(-23.54894, -46.63882), // são paulo
    'maps.center' => array(-14.2400732, -53.1805018), // brasil
    'maps.zoom.default' => 5,
    'maps.zoom.approximate' => 14,
    'maps.zoom.precise' => 16,
    'maps.zoom.max' => 18,
    'maps.zoom.min' => 5,
    'maps.includeGoogleLayers' => false,

    'app.geoDivisionsHierarchy' => array(
        'país',
        'região',
        'estado',
        'mesorregião',
        'microrregião',
        'município',
        'zona',
        'subprefeitura',
        'distrito'
    ),
    
    'registration.agentRelationsOptions' => array(
        'dontUse' => 'Não é necessário',
        'required' => 'Obrigatório',
        'optional' => 'Opcional'
    ),
    
    'registration.agentRelations' => array(
        array(
            'required' => false,
            'label' => 'Instituição',
            'agentRelationGroupName' => 'instituicao',
            'description' => 'agente coletivo com CNPJ',
            'type' => 2,
            'requiredProperties' => array('documento')
        ),
        
        array(
            'required' => false,
            'label' => 'Coletivo',
            'agentRelationGroupName' => 'coletivo',
            'description' => 'agente coletivo sem CNPJ',
            'type' => 2,
            'requiredProperties' => array()
        )
    ),

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
    'app.log.assets' => false,

    /* ==================== CACHE ================== */
    'app.cache' => new \Doctrine\Common\Cache\ApcCache(),
    'app.cache.namespace' => BASE_PATH,

    'app.useRegisteredAutoloadCache' => true,
    'app.registeredAutoloadCache.lifetime' => 0,

    'app.useAssetsUrlCache' => true,
    'app.assetsUrlCache.lifetime' => 0,

    'app.useFileUrlCache' => true,
    'app.fileUrlCache.lifetime' => 604800,
    
    'app.useEventsCache' => true,
    'app.eventsCache.lifetime' => 600,

    'app.useApiCache' => true,
    'app.apiCache.lifetime' => 120,
    
    'app.useUsersWithControlCache' => true,
    'app.usersWithControlCache.lifetime' => 30,

    'app.apiCache.lifetimeByController' => array(
        'notification' => 0,
        'event' => 25
    ),

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
            'como-usar' => array('site',    'page', array('como-usar')),

            // workflow actions
            'aprovar-notificacao' => array('notification', 'approve'),
            'rejeitar-notificacao' => array('notification', 'reject'),
            'inscrever-se' => array('registration', 'create')
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
