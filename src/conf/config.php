<?php
$config = [];

// arquivos da pasta /config
$config_files = glob(CONFIG_PATH . '*.php');
sort($config_files);
foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

// arquivos da pasta config/config.d
// esta pasta é onde devem entrar as configurações da instalação
$config_files = glob(CONFIG_PATH . 'config.d/*.php');
sort($config_files);
foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

return $config;