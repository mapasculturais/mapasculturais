<?php
namespace MapasCulturais;
define('BASE_PATH', realpath(__DIR__.'/../../') . '/');
define('PROTECTED_PATH', realpath(__DIR__.'/../') . '/');
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH',  THEMES_PATH . 'active/');
define('PLUGINS_PATH', APPLICATION_PATH.'/plugins/');

define('AUTOLOAD_TTL', 60 * 5);

define('APPMODE_DEVELOPMENT', 'development');
define('APPMODE_PRODUCTION', 'production');
define('APPMODE_STAGING', 'staging');


require_once __DIR__."/../vendor/autoload.php";

if(isset($_ENV['MAPASCULTURAIS_CONFIG_FILE'])){
    $config = include __DIR__.'/conf/' . $_ENV['MAPASCULTURAIS_CONFIG_FILE'];
}else if(isset($_SERVER['MAPASCULTURAIS_CONFIG_FILE'])){
    $config = include __DIR__.'/conf/' . $_SERVER['MAPASCULTURAIS_CONFIG_FILE'];
}else{
    $config = include __DIR__.'/conf/config.php';
}
// create the App instance
$app = App::i()->init($config);
