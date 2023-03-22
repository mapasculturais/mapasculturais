<?php
use Monolog\Level;

switch(strtoupper(env('LOG_LEVEL', 'NOTICE'))){
    case 'ALERT':
        $loglevel = Level::Alert;
        break;
    case 'CRITICAL':
        $loglevel = Level::Critical;
        break;
    case 'DEBUG':
        $loglevel = Level::Debug;
        break;
    case 'EMERGENCY':
        $loglevel = Level::Emergency;
        break;
    case 'ERROR':
        $loglevel = Level::Error;
        break;
    case 'INFO':
        $loglevel = Level::Info;
        break;
    case 'NOTICE':
        $loglevel = Level::Notice;
        break;
    case 'WARN':
        $loglevel = Level::Warning;
        break;
    default:
        $loglevel = Level::Notice;
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

    'app.log.components'          => env('LOG_COMPONENTS', false),
    
    'app.log.pcache'        => env('LOG_PCACHE', false),
    'app.log.jobs'          => env('LOG_JOBS', false),

    'app.queryLogger' => env('LOG_QUERYLOG_CLASS', null)

];