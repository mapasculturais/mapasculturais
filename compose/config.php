<?php
$config = include 'conf-base.php';

$config_files = glob(__DIR__ . '/' . 'config.d/*.php');

sort($config_files);


function env($name, $default){
    return isset($_ENV[$name]) ? $_ENV[$name] : $default;
}

foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

return $config;