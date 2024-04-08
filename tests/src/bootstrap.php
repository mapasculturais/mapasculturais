<?php

require __DIR__ . '/../../src/functions.php';

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', MINUTE_IN_SECONDS * 60);
define('DAY_IN_SECONDS', HOUR_IN_SECONDS * 24);
define('WEEK_IN_SECONDS', DAY_IN_SECONDS * 7);
define('MONTH_IN_SECONDS', DAY_IN_SECONDS * 30);
define('YEAR_IN_SECONDS', DAY_IN_SECONDS * 365 );

define('PROTECTED_PATH', realpath(__DIR__ . '/../..') . '/');
define('PUBLIC_PATH', PROTECTED_PATH . 'public/');
define('BASE_PATH', PUBLIC_PATH);
define('APPLICATION_PATH', realpath(__DIR__) . '/../../src/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('PLUGINS_PATH', APPLICATION_PATH . 'plugins/');
define('MODULES_PATH', APPLICATION_PATH . 'modules/');
define('VAR_PATH', PROTECTED_PATH . 'var/');
define('CONFIG_PATH', PROTECTED_PATH . 'config/');

define('DOCTRINE_PROXIES_PATH', VAR_PATH . 'DoctrineProxies/');
define('PRIVATE_FILES_PATH', env('PRIVATE_FILES_PATH', VAR_PATH . 'private-files/'));
define('SESSIONS_SAVE_PATH', env('SESSIONS_SAVE_PATH', VAR_PATH . 'sessions/'));

define('SESSION_TIMEOUT', intval(env('SESSION_TIMEOUT', 12 * HOUR_IN_SECONDS)));
define('REDIS_SESSION', strpos(SESSIONS_SAVE_PATH, 'tcp://') !== false);

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

// if(!is_dir(PRIVATE_FILES_PATH)){
//     mkdir(PRIVATE_FILES_PATH);
// }

// if(!is_dir(DOCTRINE_PROXIES_PATH)){
//     mkdir(DOCTRINE_PROXIES_PATH);
// }

// if (REDIS_SESSION) {
//     ini_set('session.save_handler', 'redis'); 
// } else if(!is_dir(SESSIONS_SAVE_PATH)){
//     mkdir(SESSIONS_SAVE_PATH);
// }

ini_set( "session.gc_maxlifetime", SESSION_TIMEOUT );
ini_set( "session.cookie_lifetime", SESSION_TIMEOUT );

if(!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

require_once __DIR__ . "/../../vendor/autoload.php";
// require_once PROTECTED_PATH . '../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');


 // Prepare a mock environment
// \Slim\Environment::mock(array_merge(array(
//     'REQUEST_METHOD' => 'get',
//     'PATH_INFO'      => '/',
//     'SERVER_NAME'    => 'local.dev',
// )));


// $config = include __DIR__ . '/../src/protected/application/conf/conf-test.php';

// if(isset($_ENV['MAPASCULTURAIS_CONFIG_FILE'])){
//     $config = include __DIR__ . '/../src/protected/application/conf/'. $_ENV['MAPASCULTURAIS_CONFIG_FILE'];    
// }else if(isset($_SERVER['MAPASCULTURAIS_CONFIG_FILE'])){
//     $config = include __DIR__ . '/../src/protected/application/conf/' . $_SERVER['MAPASCULTURAIS_CONFIG_FILE'];
// }


$config = require_once("config.php");

// require_once(__DIR__ . "/../../src/load-translation.php");

// create the App instance
$app = \MapasCulturais\App::i()->init($config);
// $app->register();

require __DIR__ . '/classes/TestCase.php';
require __DIR__ . '/classes/TestFactory.php';

/* End of file bootstrap.php */
