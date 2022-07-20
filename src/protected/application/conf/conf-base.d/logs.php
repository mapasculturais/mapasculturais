<?php
switch(strtoupper(env('LOG_LEVEL', 'NOTICE'))){
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
    //'slim.log.writer' => new \Custom\Log\Writer(),
    'slim.log.level'    => $loglevel,
    'slim.log.enabled'      => env('LOG_ENABLED', false),
    'app.log.path'          => env('LOG_PATH', realpath(BASE_PATH . '..') . '/logs/'),
    'app.log.query'         => env('LOG_QUERY', false),
    'app.log.hook'          => env('LOG_HOOK', false),
    'app.log.requestData'   => env('LOG_REQUESTDATA', false),
    'app.log.translations'  => env('LOG_TRANSLATIONS', false),
    'app.log.apiCache'      => env('LOG_APICACHE', false),
    'app.log.apiDql'        => env('LOG_APIDQL', false),
    'app.log.assets'        => env('LOG_ASSETS', false),
    'app.log.auth'          => env('LOG_AUTH', false),

    'app.log.pcache'        => env('LOG_PCACHE', false),
    'app.log.jobs'          => env('LOG_JOBS', false),

    'app.queryLogger' => env('LOG_QUERYLOG_CLASS', null)

];