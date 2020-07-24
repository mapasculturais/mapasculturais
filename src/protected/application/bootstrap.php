<?php
require __DIR__ . '/bootstrap-common.php';

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
