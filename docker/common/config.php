<?php
$config = include 'conf-base.php';

foreach(['conf-common.d', 'config.d'] as $folder){
    $config_files = glob(__DIR__ . "/{$folder}/*.php");
    
    sort($config_files);
    
    foreach($config_files as $config_file) {
        $config = array_merge($config, include ($config_file) );
    }
}


return $config;