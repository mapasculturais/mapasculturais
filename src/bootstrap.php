<?php
require __DIR__ . '/functions.php';

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', MINUTE_IN_SECONDS * 60);
define('DAY_IN_SECONDS', HOUR_IN_SECONDS * 24);
define('WEEK_IN_SECONDS', DAY_IN_SECONDS * 7);
define('MONTH_IN_SECONDS', DAY_IN_SECONDS * 30);
define('YEAR_IN_SECONDS', DAY_IN_SECONDS * 365 );

define('PROTECTED_PATH', realpath(__DIR__ . '/..') . '/');
define('PUBLIC_PATH', PROTECTED_PATH . 'public/');
define('BASE_PATH', PUBLIC_PATH);
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('PLUGINS_PATH', APPLICATION_PATH . 'plugins/');
define('MODULES_PATH', APPLICATION_PATH . 'modules/');
define('VAR_PATH', PROTECTED_PATH . 'var/');
define('LOGS_PATH', VAR_PATH . 'logs/');
define('CONFIG_PATH', PROTECTED_PATH . 'config/');

define('DOCTRINE_PROXIES_PATH', VAR_PATH . 'DoctrineProxies/');
define('PRIVATE_FILES_PATH', env('PRIVATE_FILES_PATH', VAR_PATH . 'private-files/'));
$session_save_path = env('SESSIONS_SAVE_PATH', VAR_PATH . 'sessions/');
if (strpos($session_save_path, 'tcp://') !== false) {
    $session_save_path = preg_replace('/^tcp:\/\//', '', $session_save_path);
}
define('SESSIONS_SAVE_PATH', $session_save_path);

define('SESSION_TIMEOUT', intval(env('SESSION_TIMEOUT', 12 * HOUR_IN_SECONDS)));
define('REDIS_SESSION', strpos($session_save_path, ':') !== false && strpos($session_save_path, '/') === false);

define('AUTOLOAD_TTL', 5 * MINUTE_IN_SECONDS);

define('APPMODE_DEVELOPMENT', 'development');
define('APPMODE_PRODUCTION', 'production');
define('APPMODE_STAGING', 'staging');

if($timezone = env('TIMEZONE')) {
    date_default_timezone_set($timezone);
}

if (env('MAPAS_HTTPS', false) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';
    $_SERVER['REQUEST_SCHEME'] = 'https';
}

if(!is_dir(PRIVATE_FILES_PATH)){
   throw new Exception("Folder ". PRIVATE_FILES_PATH ."  is required");
}

if(!is_dir(DOCTRINE_PROXIES_PATH)){
    mkdir(DOCTRINE_PROXIES_PATH, 0755);
}

if (REDIS_SESSION) {
    ini_set('session.save_handler', 'redis'); 
} else if(!is_dir(SESSIONS_SAVE_PATH)){
    mkdir(SESSIONS_SAVE_PATH);
}

ini_set( "session.gc_maxlifetime", SESSION_TIMEOUT );
ini_set( "session.cookie_lifetime", SESSION_TIMEOUT );

if(!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
require_once PROTECTED_PATH . 'vendor/autoload.php';
