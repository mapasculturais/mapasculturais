<?php
require __DIR__ . '/../src/bootstrap.php';

$config_filename = APPLICATION_PATH . 'conf/config.php';

require APPLICATION_PATH . "load-translation.php";

$config = require $config_filename;

$config['app.lcode'] = $lcode;

if(($_SERVER['CONTENT_TYPE'] ?? '') == 'application/json') {
    $json = file_get_contents('php://input');
    $decoded = json_decode($json, true);
    if($decoded) {
        $_POST = $decoded;
    }
}
// create the App instance
$app = MapasCulturais\App::i('web');
$app->init($config);
