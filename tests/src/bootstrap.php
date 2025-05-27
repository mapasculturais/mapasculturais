<?php

use MapasCulturais\App;

const TESTS_PATH = __DIR__ . '/';
require_once TESTS_PATH . '../src/bootstrap.php';
require_once TESTS_PATH . 'TestCase.php';

$config_filename = APPLICATION_PATH . 'conf/config.php';
require APPLICATION_PATH . "load-translation.php";

$config = require $config_filename;

$config['app.lcode'] = $lcode;
$app = App::i();
$app->init($config);