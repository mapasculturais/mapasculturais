<?php
require __DIR__ . '/functions.php';

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', MINUTE_IN_SECONDS * 60);
define('DAY_IN_SECONDS', HOUR_IN_SECONDS * 24);
define('WEEK_IN_SECONDS', DAY_IN_SECONDS * 7);
define('MONTH_IN_SECONDS', DAY_IN_SECONDS * 30);
define('YEAR_IN_SECONDS', DAY_IN_SECONDS * 365 );

define('BASE_PATH', realpath(__DIR__ . '/../../') . '/');
define('PROTECTED_PATH', realpath(__DIR__ . '/../') . '/');
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH', THEMES_PATH . 'active/');
define('PLUGINS_PATH', APPLICATION_PATH . 'plugins/');
define('MODULES_PATH', APPLICATION_PATH . 'lib/modules/');

define('DOCTRINE_PROXIES_PATH', PROTECTED_PATH . 'DoctrineProxies/');

define('PRIVATE_FILES_PATH', env('PRIVATE_FILES_PATH', dirname(BASE_PATH) . '/private-files/'));

define('SESSIONS_SAVE_PATH', env('SESSIONS_SAVE_PATH', PRIVATE_FILES_PATH . 'sessions/'));
define('SESSION_TIMEOUT', intval(env('SESSION_TIMEOUT', 2 * HOUR_IN_SECONDS)));
define('REDIS_SESSION', strpos(SESSIONS_SAVE_PATH, 'tcp://') !== false);

define('AUTOLOAD_TTL', 5 * MINUTE_IN_SECONDS);

define('APPMODE_DEVELOPMENT', 'development');
define('APPMODE_PRODUCTION', 'production');
define('APPMODE_STAGING', 'staging');


if (env('MAPAS_HTTPS', false) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';
    $_SERVER['REQUEST_SCHEME'] = 'https';
}

if(!is_dir(PRIVATE_FILES_PATH)){
    mkdir(PRIVATE_FILES_PATH);
}

if(!is_dir(DOCTRINE_PROXIES_PATH)){
    mkdir(DOCTRINE_PROXIES_PATH);
}

if (REDIS_SESSION) {
    ini_set('session.save_handler', 'redis'); 
} else if(!is_dir(SESSIONS_SAVE_PATH)){
    mkdir(SESSIONS_SAVE_PATH);
}

ini_set( "session.gc_maxlifetime", SESSION_TIMEOUT );
ini_set( "session.cookie_lifetime", SESSION_TIMEOUT );