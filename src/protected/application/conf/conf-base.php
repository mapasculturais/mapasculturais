<?php

$config = [];

$config_files = glob(__DIR__ . '/conf-base.d/*.php');

sort($config_files);

foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

if(defined('GENERATING_CONFIG_DOCUMENTATION')){
    die;
}

return $config;
