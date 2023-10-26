<?php
$config = [];

// arquivos da pasta /config
$config_files = glob(CONFIG_PATH . '*.php');
sort($config_files);
foreach($config_files as $config_file) {
    $config = array_merge($config, include ($config_file) );
}

// inclui arquivos .php de todas as pastas terminadas com .d
// dentro da pastas /config. 
$folders = glob(CONFIG_PATH . '*.d/');
sort($folders);
foreach($folders as $folder) {
    $config_files = glob($folder . '*.php');
    sort($config_files);
    foreach($config_files as $config_file) {
        $config = array_merge($config, include ($config_file) );
    }
}

return $config;