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
    'monolog.defaultLevel' => env('LOG_LEVEL', 'DEBUG'),

    /*
    Configuração dos handlers do monolog.

    se for uma string os handlers serão instanciados pela aplicação,
    se for um array, a aplicação esperará que seja uma lista de handlers

    ex: 'file:WARNING,error_log:DEBUG,browser:DEBUG'
    ex: [new \Monolog\Handler\ErrorLogHandler(level: Level::Debug)]
    */
    'monolog.handlers' => env('LOG_HANDLERS', 'error_log,browser'),

    'monolog.processors' => [],

    /*
     Pasta onde serão salvos os arquivos de log
     
     o padrão é ~/var/logs, onde ~ é a raíz do projeto, no docker é /var/www
     */
    'monolog.logsDir'          => env('LOG_DIR', VAR_PATH . 'logs/'),

    'app.log.hook.traceDepth' => 5,
     
    // 'app.log.hook'          => true,
    // 'app.log.query'         => true,
    // 'app.log.requestData'   => true,
    // 'app.log.texts'         => true,
    // 'app.log.translations'  => true,
    // 'app.log.apiCache'      => true,
    // 'app.log.apiDql'        => true,
    // 'app.log.assets'        => true,
    // 'app.log.auth'          => true,

    // 'app.log.components'    => true,
    // 'app.log.assetManager'  => true,
    
    // 'app.log.jobs'          => true,
    // 'app.log.pcache'        => true,
    // 'app.log.pcache.users'  => true,

    'app.queryLogger' => env('LOG_QUERYLOG_CLASS', null)

];