<?php
$config = include 'conf-base.php';

$config_files = glob(__DIR__ . '/' . 'config.d/*.php');

sort($config_files);

foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

return $config;