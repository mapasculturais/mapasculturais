<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = include 'conf-base.php';

return array_merge($config,
    array(
        'base.url' => 'http://localhost:8000/',
        // development, staging, production
        'app.mode' => 'development',
        'app.fakeAuthentication' => true
    )
);
