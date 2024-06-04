<?php
return [
    /*
    Nível do log do monolog

    DEBUG (100): Detailed debug information.
    INFO (200): Interesting events. Examples: User logs in, SQL logs.
    NOTICE (250): Normal but significant events.
    WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
    ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
    CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
    ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
    EMERGENCY (600): Emergency: system is unusable.
    */
    'monolog.defaultLevel' => env('LOG_LEVEL', 'WARNING'),

    /*
    Configuração dos handlers do monolog.

    se for uma string os handlers serão instanciados pela aplicação,
    se for um array, a aplicação esperará que seja uma lista de handlers

    ex: 'file:WARNING,error_log:DEBUG,browser:DEBUG'
    ex: [new \Monolog\Handler\ErrorLogHandler(level: Level::Debug)]
    */
    'monolog.handlers' => env('LOG_HANDLERS', 'file:WARNING,error_log:DEBUG,telegram:CRITICAL'),

    'monolog.processors' => [],

    /*
    Chave de api do bot do telegram para o monolog
    */
    'monolog.telegram.apiKey' => env('LOG_TELEGRAM_API_KEY'),

    /*
    Id do canal do telegram onde o bot deve enviar as mensagens
    */
    'monolog.telegram.channelId' => env('LOG_TELEGRAM_CHANNELID'),

    /*
     Pasta onde serão salvos os arquivos de log
     
     o padrão é ~/var/logs, onde ~ é a raíz do projeto, no docker é /var/www
     */
    'monolog.logsDir'          => env('LOG_DIR', VAR_PATH . 'logs/'),
    
    'app.log.hook.traceDepth' => env('LOG_HOOK_TRACE_DEPTH', 5),

    'app.log.query'         => env('LOG_QUERY', false),
    'app.log.hook'          => env('LOG_HOOK', false),
    'app.log.requestData'   => env('LOG_REQUESTDATA', false),
    'app.log.texts'         => env('LOG_TEXTS', false),
    'app.log.translations'  => env('LOG_TRANSLATIONS', false),
    'app.log.apiCache'      => env('LOG_APICACHE', false),
    'app.log.apiDql'        => env('LOG_APIDQL', false),
    'app.log.assets'        => env('LOG_ASSETS', false),
    'app.log.auth'          => env('LOG_AUTH', false),

    'app.log.components'          => env('LOG_COMPONENTS', false),
    'app.log.assetManager'        => env('LOG_ASSETMANAGER', false),
    
    'app.log.jobs'          => env('LOG_JOBS', false),
    'app.log.pcache'        => env('LOG_PCACHE', false),
    'app.log.pcache.users'  => env('LOG_PCACHE_USERS', false),

    'app.queryLogger' => env('LOG_QUERYLOG_CLASS', null)

];