<?php
$config = include 'conf-base.php';

$config_files = glob(__DIR__ . '/' . 'config.d/*.php');

sort($config_files);


function env($name, $default){
    $result = isset($_ENV[$name]) ? $_ENV[$name] : $default;

    if(strtolower(trim($result)) == 'true'){
        $result = true;
    } else if(strtolower(trim($result)) == 'false'){
        $result = false;
    }

    return $result;
}

foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

return $config;