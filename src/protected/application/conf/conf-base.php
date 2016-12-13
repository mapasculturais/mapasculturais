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

    'mailer.user' => "admin@mapasculturais.org",
    'mailer.psw'  => "password",
    'mailer.protocol' => 'ssl',
    'mailer.server' => 'smtp.gmail.com',
    'mailer.port'   => '465',
    'mailer.from' => 'suporte@mapasculturais.org',

    // sempre colocar a barra no final da url
    'base.url' => $base_url,
    'base.assetUrl' => $base_url . $asset_dir,

    'vectorLayersPath' => 'geojson',

    // development, staging, production
    'app.mode' => 'production',
    'app.lcode' => 'pt-br',

    'app.verifiedSealsIds' => [1],

    'app.dbUpdatesDisabled' => false,
    'app.defaultApiOutput' => 'json',

    'app.siteName' => \MapasCulturais\i::__('Mapas Culturais'),
    'app.siteDescription' => \MapasCulturais\i::__('O Mapas Culturais é uma plataforma livre para mapeamento cultural.'),

    'api.accessControlAllowOrigin' => '*',

    'app.enableProjectRegistration' => true,

    'app.offline' => false,
    'app.offlineUrl' => '/offline',
    'app.offlineBypassFunction' => null,

    'app.enabled.agents'   => true,
    'app.enabled.spaces'   => true,
    'app.enabled.projects' => true,
    'app.enabled.events'   => true,
    'app.enabled.seals'   => true,
    'app.enabled.apps'     => true,

    'themes.active' => 'MapasCulturais\Themes\BaseV1',
    'themes.active.debugParts' => true,
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(array(
        'publishPath' => BASE_PATH . $asset_dir,

        'mergeScripts' => false,
        'mergeStyles' => false,

        'process.js' => 'cp {IN} {OUT}', //'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /pub/{FILENAME}.map -b -p 7 && gzip -9 -c {OUT} > {OUT}.gz',
        'process.css' => 'cp {IN} {OUT}', //'uglifycss {IN} > {OUT} && gzip -9 -c {OUT} > {OUT}.gz',
        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    )),

    'app.useGoogleGeocode' => false,

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

    'cep.endpoint'      => 'http://www.cepaberto.com/api/v2/ceps.json?cep=%s',
    'cep.token_header'  => 'Authorization: Token token="%s"',
    'cep.token'         => '',

    'export.excelName'      => 'mapas-culturais-dados-exportados.xls',

    'app.geoDivisionsHierarchy' => [
        'pais'          => \MapasCulturais\i::__('País'),          // metadata: geoPais
        'regiao'        => \MapasCulturais\i::__('Região'),        // metadata: geoRegiao
        'estado'        => \MapasCulturais\i::__('Estado'),        // metadata: geoEstado
        'mesorregiao'   => \MapasCulturais\i::__('Mesorregião'),   // metadata: geoMesorregiao
        'microrregiao'  => \MapasCulturais\i::__('Microrregião'),  // metadata: geoMicrorregiao
        'municipio'     => \MapasCulturais\i::__('Município'),     // metadata: geoMunicipio
        'zona'          => \MapasCulturais\i::__('Zona'),          // metadata: geoZona
        'subprefeitura' => \MapasCulturais\i::__('Subprefeitura'), // metadata: geoSubprefeitura
        'distrito'      => \MapasCulturais\i::__('Distrito')       // metadata: geoDistrito
    ],

    'registration.agentRelationsOptions' => array(
        'dontUse' => \MapasCulturais\i::__('Não utilizar'),
        'required' => \MapasCulturais\i::__('Obrigatório'),
        'optional' => \MapasCulturais\i::__('Opcional')
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
        'label' => \MapasCulturais\i::__('Agente responsável pela inscrição'),
        'agentRelationGroupName' => 'owner',
        'description' => \MapasCulturais\i::__('Agente individual (pessoa física) com os campos CPF, Data de Nascimento/Fundação, Email Privado e Telefone 1 obrigatoriamente preenchidos'),
        'type' => 1,
        'requiredProperties' => array('documento', 'raca', 'dataDeNascimento', 'genero', 'emailPrivado', 'telefone1')
    ),
    'registration.agentRelations' => array(
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Instituição responsável'),
            'agentRelationGroupName' => 'instituicao',
            'description' => \MapasCulturais\i::__('Agente coletivo (pessoa jurídica) com os campos CNPJ, Data de Nascimento/Fundação, Email Privado e Telefone 1 obrigatoriamente preenchidos'),
            'type' => 2,
            'requiredProperties' => array('documento', 'dataDeNascimento', 'emailPrivado', 'telefone1')
        ),
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Coletivo'),
            'agentRelationGroupName' => 'coletivo',
            'description' => \MapasCulturais\i::__('Agente coletivo sem CNPJ, com os campos Data de Nascimento/Fundação e Email Privado obrigatoriamente preenchidos'),
            'type' => 2,
            'requiredProperties' => array('dataDeNascimento', 'emailPrivado')
        )
    ),

    /* ============ ENTITY PROPERTIES SEALS ============= */
    'app.entityPropertiesLabels' => array(
        '@default' => array(
            'id' => \MapasCulturais\i::__('Id'),
            'name' => \MapasCulturais\i::__('Nome'),
            'createTimestamp' => \MapasCulturais\i::__('Data de Criação'),
            'shortDescription' => \MapasCulturais\i::__('Descrição Curta'),
            'longDescription' => \MapasCulturais\i::__('Descrição Longa'),
            'certificateText' => \MapasCulturais\i::__('Conteúdo da Impressão do Certificado'),
            'validPeriod'	=> \MapasCulturais\i::__('Período de Validade'),
            'status' => \MapasCulturais\i::__('Status')
        ),

//        'MapasCulturais\Entities\Agent' => array()
    ),

    // 'app.projectRegistrationAgentRelationGroupName' \MapasCulturais\i::__(Inscrições"),

    'notifications.interval'        => 60,  // seconds
    'notifications.entities.update' => 90,  // days
    'notifications.user.access'     => 90,  // days

    /* ==================== LOG ================== */
    // write log messages to a custom output (the class must implement the method "public write(mixed $message, int $level)")
    //'slim.log.writer' => new \Custom\Log\Writer(),

    'slim.log.level' => \Slim\Log::NOTICE,
    'slim.log.enabled' => false,

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
    'app.cache' => function_exists('apcu_add') ?
        new \Doctrine\Common\Cache\ApcuCache() :
        (
            function_exists('apc_add') ?
                new \Doctrine\Common\Cache\ApcCache() :
                new \Doctrine\Common\Cache\FilesystemCache('/tmp/CACHE--' . str_replace(':', '_', @$_SERVER['HTTP_HOST']))

        ),

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
    'plugins' => [
        'ProjectPhases' => ['namespace' => 'ProjectPhases'],
        'AgendaSingles' => ['namespace' => 'AgendaSingles'],
        //['namespace' => 'PluginNamespace', 'path' => 'path/to/plugin', 'config' => ['plugin' => 'config']]
    ],

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
        	'selo'     	=> array('seal',    'single'),
            'sair'      => array('auth',    'logout'),
            'busca'     => array('site',    'search'),
            'sobre'     => array('site',    'page', array('sobre')),
            'como-usar' => array('site',    'page', array('como-usar')),

            // workflow actions
            'aprovar-notificacao' => array('notification', 'approve'),
            'rejeitar-notificacao' => array('notification', 'reject'),

            'inscricao' => array('registration', 'view'),
            'certificado' => array('relatedSeal','single'),

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
            'selos'          => 'seal',
            'inscricoes'     => 'registration',
            'anexos'         => 'registrationfileconfiguration',
        ),
        'actions' => array(
            'lista'         => 'list',
            'apaga'         => 'delete',
            'edita'         => 'edit',
            'espacos'       => 'spaces',
            'agentes'       => 'agents',
            'eventos'       => 'events',
            'projetos'      => 'projects',
            'selos'         => 'seals',
            'inscricoes'    => 'registrations'
        ),

        'readableNames' => array(
            //controllers
                'panel'         => \MapasCulturais\i::__('Painel'),
                'auth'          => \MapasCulturais\i::__('Autenticação'),
                'site'          => \MapasCulturais\i::__('Site'),
                'event'         => \MapasCulturais\i::__('Evento'),    'events'        => \MapasCulturais\i::__('Eventos'),
                'agent'         => \MapasCulturais\i::__('Agente'),    'agents'        => \MapasCulturais\i::__('Agentes'),
                'space'         => \MapasCulturais\i::__('Espaço'),    'spaces'        => \MapasCulturais\i::__('Espaços'),
                'seal'          => \MapasCulturais\i::__('Selo'),      'seals'         => \MapasCulturais\i::__('Selos'),
                'project'       => \MapasCulturais\i::__('Projeto'),   'projects'      => \MapasCulturais\i::__('Projetos'),
                'registration'  => \MapasCulturais\i::__('Inscrição'), 'registrations' => \MapasCulturais\i::__('Inscrições'),
                'file'          => \MapasCulturais\i::__('Arquivo'),   'files'         => \MapasCulturais\i::__('Arquivos'),
            //actions
                'list'          => \MapasCulturais\i::__('Listando'),
                'index'         => \MapasCulturais\i::__('Índice'),
                'delete'        => \MapasCulturais\i::__('Apagando'),
                'edit'          => \MapasCulturais\i::__('Editando'),
                'create'        => \MapasCulturais\i::__('Criando novo'),
                'search'        => \MapasCulturais\i::__('Busca')
        )
    )
);
