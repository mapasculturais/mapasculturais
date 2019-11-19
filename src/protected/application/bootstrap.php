<?php
define('BASE_PATH', realpath(__DIR__ . '/../../') . '/');
define('PROTECTED_PATH', realpath(__DIR__ . '/../') . '/');
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH', THEMES_PATH . 'active/');
define('PLUGINS_PATH', APPLICATION_PATH . 'plugins/');
define('MODULES_PATH', APPLICATION_PATH . 'lib/modules/');

define('AUTOLOAD_TTL', 60 * 5);

define('APPMODE_DEVELOPMENT', 'development');
define('APPMODE_PRODUCTION', 'production');
define('APPMODE_STAGING', 'staging');

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';
    $_SERVER['REQUEST_SCHEME'] = 'https';
}

function env($name, $default) {
    $result = isset($_ENV[$name]) ? $_ENV[$name] : $default;

    if (strtolower(trim($result)) == 'true') {
        $result = true;
    } else if (strtolower(trim($result)) == 'false') {
        $result = false;
    }

    return $result;
}

require_once __DIR__ . "/../vendor/autoload.php";

require __DIR__ . "/dump-function.php";

if (isset($_ENV['MAPASCULTURAIS_CONFIG_FILE'])) {
    $config_filename = __DIR__ . '/conf/' . $_ENV['MAPASCULTURAIS_CONFIG_FILE'];
} else if (isset($_SERVER['MAPASCULTURAIS_CONFIG_FILE'])) {
    $config_filename = __DIR__ . '/conf/' . $_SERVER['MAPASCULTURAIS_CONFIG_FILE'];
} else {
    $config_filename = __DIR__ . '/conf/config.php';
}

require __DIR__ . "/load-translation.php";

$config = require $config_filename;

$config['app.lcode'] = $lcode;

// create the App instance
$app = MapasCulturais\App::i()->init($config);
