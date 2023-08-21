<?php
require __DIR__ . '/../src/bootstrap.php';

$config_filename = APPLICATION_PATH . 'conf/config.php';

require APPLICATION_PATH . "load-translation.php";

$config = require $config_filename;

$config['app.lcode'] = $lcode;

$app = MapasCulturais\App::i('web');
$app->init($config);
