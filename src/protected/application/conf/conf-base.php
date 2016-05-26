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

    'app.siteName' => 'Mapas Culturales',
    'app.siteDescription' => 'Mapas Culturales es una plataforma libre para mapeamento cultural.',

    'api.accessControlAllowOrigin' => '*',

    'app.enableProjectRegistration' => true,

    'app.offline' => false,
    'app.offlineUrl' => '/offline',
    'app.offlineBypassFunction' => null,
    
    'app.enabled.agents'   => true,
    'app.enabled.spaces'   => true,
    'app.enabled.projects' => true,
    'app.enabled.events'   => true,
    'app.enabled.apps'     => true,

    'themes.active' => 'MapasCulturais\Themes\BaseV1',
    'themes.active.debugParts' => false,
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(array(
        'publishPath' => BASE_PATH . $asset_dir,

        'mergeScripts' => false,
        'mergeStyles' => false,

        'process.js' => 'cp {IN} {OUT}', //'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /pub/{FILENAME}.map -b -p 7 && gzip -9 -c {OUT} > {OUT}.gz',
        'process.css' => 'cp {IN} {OUT}', //'uglifycss {IN} > {OUT} && gzip -9 -c {OUT} > {OUT}.gz',
        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    )),

//    'maps.center' => array(-23.54894, -46.63882), // são paulo
    'maps.center' => array(-14.2400732, -53.1805018), // brasil
    'maps.maxClusterRadius' => 40,
    'maps.spiderfyDistanceMultiplier' => 1.3,
    'maps.maxClusterElements' => 6,

    'maps.geometryFieldQuery' => "ST_SimplifyPreserveTopology(geom, 0.001)",

    'maps.zoom.default' => 5,
    'maps.zoom.approximate' => 14,
    'maps.zoom.precise' => 16,
    'maps.zoom.max' => 18,
    'maps.zoom.min' => 5,
    'maps.includeGoogleLayers' => false,

    'app.geoDivisionsHierarchy' => [
        'pais'          => 'País',          // metadata: geoPais
        'regiao'        => 'Región',        // metadata: geoRegiao
        'estado'        => 'Estado',        // metadata: geoEstado
        'mesorregiao'   => 'Mesorregión',   // metadata: geoMesorregiao
        'microrregiao'  => 'Microrregión',  // metadata: geoMicrorregiao
        'municipio'     => 'Municipio',     // metadata: geoMunicipio
        'zona'          => 'Zona',          // metadata: geoZona
        'subprefeitura' => 'Alcaldía', // metadata: geoSubprefeitura
        'distrito'      => 'Distrito'       // metadata: geoDistrito
    ],

    'registration.agentRelationsOptions' => array(
        'dontUse' => 'No utilizar',
        'required' => 'Obligatorio',
        'optional' => 'Opcional'
    ),
    'registration.propertiesToExport' => array(
        'id',
        'name',
        'nomeCompleto',
        'documento',
        'dataDeNascimento',
        'genero',
        'raca',
        'location',
        'endereco',
        'geoZona',
        'geoSubprefeitura',
        'geoDistrito',
        'telefone1',
        'telefone2',
        'telefonePublico',
        'emailPrivado',
        'emailPublico',
        'site',
        'googleplus',
        'facebook',
        'twitter'
    ),
    'registration.ownerDefinition' => array(
        'required' => true,
        'label' => 'Agente responsable por la inscripción',
        'agentRelationGroupName' => 'owner',
        'description' => 'Agente individual (persona física) con los campos CI, Fecha de nacimiento/fundación, Género, Email Privado y Teléfono 1 obligatoriamente completados',
        'type' => 1,
       // 'requiredProperties' => array('documento', 'raca', 'dataDeNascimento', 'genero', 'emailPrivado', 'telefone1')
    	'requiredProperties' => array('documento', 'dataDeNascimento', 'genero', 'emailPrivado', 'telefone1')
    ),
    'registration.agentRelations' => array(
        array(
            'required' => false,
            'label' => 'Institución responsable',
            'agentRelationGroupName' => 'instituicao',
            'description' => 'Agente colectivo (persona jurídica) con los campos RUT, Fecha de nacimiento/fundación, Email Privado y Teléfono 1 obligatoriamente completados',
            'type' => 2,
            'requiredProperties' => array('documento', 'dataDeNascimento', 'emailPrivado', 'telefone1')
        ),
        array(
            'required' => false,
            'label' => 'Colectivo',
            'agentRelationGroupName' => 'coletivo',
            'description' => 'Agente colectivo sin RUT, con los campos Fecha de nacimiento/fundación e Email Privado obligatoriamente completados',
            'type' => 2,
            'requiredProperties' => array('dataDeNascimento', 'emailPrivado')
        )
    ),

    /* ============ ENTITY PROPERTIES LABELS ============= */
    'app.entityPropertiesLabels' => array(
        '@default' => array(
            'id' => 'Id',
            'name' => 'Nombre',
            'createTimestamp' => 'Fecha de creación',
            'shortDescription' => 'Descripción corta',
            'longDescription' => 'Descripción larga',
            'status' => 'Estatus',
            'location' => 'Coordenada Geográfica',
            '_type' => 'Tipo'
        ),

//        'MapasCulturais\Entities\Agent' => array()
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
    'app.cache.namespace' => @$_SERVER['HTTP_HOST'],
    
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

    'app.usePermissionsCache' => true,
    'app.permissionsCache.lifetime' => 120,

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
             'james-bond'                => array('agent', 'single', array('id' => 7)),
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
            'inscricao' => array('registration', 'view'),
        ),
        'controllers' => array(
            'painel'         => 'panel',
            'autenticacao'   => 'auth',
            'site'           => 'site',
            'eventos'        => 'event',
            'agentes'        => 'agent',
            'espacos'        => 'space',
            'arquivos'       => 'file',
            'projetos'       => 'project',
            'inscricoes'     => 'registration',
            'anexos'         => 'registrationfileconfiguration'
        ),
        'actions' => array(
            'lista'         => 'list',
            'apaga'         => 'delete',
            'edita'         => 'edit',
            'espacos'       => 'spaces',
            'agentes'       => 'agents',
            'eventos'       => 'events',
            'projetos'      => 'projects',
            'inscricoes'    => 'registrations'
        ),

        'readableNames' => array(
            //controllers
                'panel'         => 'Painel',
                'auth'          => 'Autenticação',
                'site'          => 'Site',
                'event'         => 'Evento',    'events'        => 'Eventos',
                'agent'         => 'Agente',    'agents'        => 'Agentes',
                'space'         => 'Espaço',    'spaces'        => 'Espaços',
                'project'       => 'Projeto',   'projects'      => 'Projetos',
                'registration'  => 'Inscrição', 'registrations' => 'Inscripciones',
                'file'          => 'Arquivo',   'files'         => 'Arquivos',
            //actions
                'list'          => 'Listando',
                'index'         => 'Índice',
                'delete'        => 'Apagando',
                'edit'          => 'Editando',
                'create'        => 'Criando novo',
                'search'        => 'Busca'
        )
    )
);
