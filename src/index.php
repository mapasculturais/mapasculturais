<?php
require_once __DIR__.'/protected/application/bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

MapasCulturais\App::i()->run();
