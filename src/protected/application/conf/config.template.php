<?php

$config = include 'conf-base.php';

return array_merge($config,
    array(
        'base.url' => 'http://localhost:8000/',
        // development, staging, production
        'app.mode' => 'development',
        'app.fakeAuthentication' => true
    )
);
