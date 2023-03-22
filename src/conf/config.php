<?php
$config = [];

$config_files = glob(PROTECTED_PATH . 'config/*.php');

sort($config_files);

foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

return $config;