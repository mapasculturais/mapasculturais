<?php
switch(env('LOG_LEVEL', 'NOTICE')){
    case 'ALERT':
        $loglevel = \Slim\Log::ALERT;
        break;
    case 'CRITICAL':
        $loglevel = \Slim\Log::CRITICAL;
        break;
    case 'DEBUG':
        $loglevel = \Slim\Log::DEBUG;
        break;
    case 'EMERGENCY':
        $loglevel = \Slim\Log::EMERGENCY;
        break;
    case 'ERROR':
        $loglevel = \Slim\Log::ERROR;
        break;
    case 'FATAL':
        $loglevel = \Slim\Log::FATAL;
        break;
    case 'INFO':
        $loglevel = \Slim\Log::INFO;
        break;
    case 'NOTICE':
        $loglevel = \Slim\Log::NOTICE;
        break;
    case 'WARN':
        $loglevel = \Slim\Log::WARN;
        break;
    default:
        $loglevel = \Slim\Log::NOTICE;
        break;
}

return [
    'app.siteName' => env('SITE_NAME', 'Mapas Culturais'),
    'app.siteDescription' => env('SITE_DESCRIPTION', ''),

    'themes.active' => env('ACTIVE_THEME', 'MapasCulturais\Themes\BaseV1'),

    'app.lcode' => env('APP_LCODE', 'pt_BR'),

    // APP MODE
    'app.mode' => env('APP_MODE', 'production'),
    'doctrine.isDev' => env('DOCTRINE_ISDEV', false),
    'slim.debug' => env('SLIM_DEBUG', false),

    // DATABASE
    'doctrine.database' => [
        'dbname' => env('DB_NAME', 'mapas'),
        'user' => env('DB_USER', 'mapas'),
        'host' => env('DB_HOST', 'db'),
        'password' => env('DB_PASS', 'mapas'),
        'server_version'  => 10.0,
    ],

    // MAILER
    'mailer.user' => env('MAILER_USER', "admin@mapasculturais.org"),
    'mailer.psw'  => env('MAILER_PASS', "password"),
    'mailer.protocol' => env('MAILER_PROTOCOL', 'ssl'),
    'mailer.server' => env('MAILER_SERVER', 'localhost'),
    'mailer.port'   => env('MAILER_PORT', '465'),
    'mailer.from' => env('MAILER_FROM', 'suporte@mapasculturais.org'),
    'mailer.alwaysTo' => env('MAILER_ALWAYSTO', false),

    // MAP
    'maps.zoom.default' => env('MAPS_ZOOM_DEFAULTS', 5),
    'maps.zoom.approximate' => env('MAPS_ZOOM_APPROXIMATE', 14),
    'maps.zoom.precise' => env('MAPS_ZOOM_PRECISE', 16),
    'maps.zoom.max' => env('MAPS_ZOOM_MAX', 18),
    'maps.zoom.min' => env('MAPS_ZOOM_MIN', 5),
    'maps.includeGoogleLayers' => env('MAPS_INCLUDE_GOOGLE_LAYERS', false),

    // CEP API
    'cep.endpoint'      => env('CEP_ENDPOINT', 'http://www.cepaberto.com/api/v2/ceps.json?cep=%s'),
    'cep.token_header'  => env('CEP_TOKEN_HEADER', 'Authorization: Token token="%s"'),
    'cep.token'         => env('CEP_TOKEN', ''),

    // LOG
    'slim.log.level' => $loglevel,
    'slim.log.enabled' => env('LOG_ENABLED', false),

    'app.log.path' => env('LOG_PATH', realpath(BASE_PATH . '..') . '/logs/'),
    'app.log.query' => env('LOG_QUERY', false),
    'app.log.hook' => env('LOG_HOOK', false),
    'app.log.requestData' => env('LOG_REQUESTDATA', false),
    'app.log.translations' => env('LOG_TRANSLATIONS', false),
    'app.log.apiCache' => env('LOG_APICACHE', false),
    'app.log.apiDql' => env('LOG_APIDQL', false),
    'app.log.assets' => env('LOG_ASSETS', false),
    
];