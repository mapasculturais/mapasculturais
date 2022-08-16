<?php
define('BASE_PATH', realpath(__DIR__ . '/../../') . '/');
define('PROTECTED_PATH', realpath(__DIR__ . '/../') . '/');
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('LANGUAGES_PATH', APPLICATION_PATH . 'translations/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH', THEMES_PATH . 'active/');
define('PLUGINS_PATH', APPLICATION_PATH . 'plugins/');
define('MODULES_PATH', APPLICATION_PATH . 'lib/modules/');

define('DOCTRINE_PROXIES_PATH', PROTECTED_PATH . 'DoctrineProxies/');
if(!is_dir(DOCTRINE_PROXIES_PATH)){
    mkdir(DOCTRINE_PROXIES_PATH);
}

define('PRIVATE_FILES_PATH', env('PRIVATE_FILES_PATH', dirname(BASE_PATH) . '/private-files/'));
define('SESSIONS_SAVE_PATH', env('SESSIONS_SAVE_PATH', PRIVATE_FILES_PATH . 'sessions/'));

define('REDIS_SESSION', strpos(SESSIONS_SAVE_PATH, 'tcp://') !== false);

if(!is_dir(PRIVATE_FILES_PATH)){
    mkdir(PRIVATE_FILES_PATH);
}

if (REDIS_SESSION) {
    ini_set('session.save_handler', 'redis'); 
} else {
    if(!is_dir(SESSIONS_SAVE_PATH)){
            mkdir(SESSIONS_SAVE_PATH);
    }
}

define('AUTOLOAD_TTL', 60 * 5);

define('APPMODE_DEVELOPMENT', 'development');
define('APPMODE_PRODUCTION', 'production');
define('APPMODE_STAGING', 'staging');

define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', MINUTE_IN_SECONDS * 60);
define('DAY_IN_SECONDS', HOUR_IN_SECONDS * 24);
define('WEEK_IN_SECONDS', DAY_IN_SECONDS * 7);
define('MONTH_IN_SECONDS', DAY_IN_SECONDS * 30);
define('YEAR_IN_SECONDS', DAY_IN_SECONDS * 365 );


function env($name, $default = null) {
    if(defined('GENERATING_CONFIG_DOCUMENTATION')){
        __log_env($name, $default);
    }

    $result = isset($_ENV[$name]) ? $_ENV[$name] : $default;

    if (strtolower(trim($result)) == 'true') {
        $result = true;
    } else if (strtolower(trim($result)) == 'false') {
        $result = false;
    }

    return $result;
}

function __env_not_false($var_name){
    return strtolower(env($var_name, 0)) !== 'false';
}


function __log_env($name,$default){
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    
    $filename = $bt[1]['file'];
    $fileline = $bt[1]['line'];
    $lines = file($filename);
    $line = trim($lines[$fileline - 1]);

    if(preg_match("#'([\w\d\.]+)' *=> *env\('{$name}', *(.*?)\),#", $line, $matches)){
        $config = $matches[1];
        $default = $matches[2];
    }
    if(!$config){
        return;
    }
    $_lines = implode("", array_slice($lines, max($fileline - 20, 0), min(19, $fileline-1)));
    $_preg_line = preg_quote($line, '#');
    $_pattern = "#\/\*((\*(?!\/)|[^*])*)\*\/$#";
    
    $description = '';
    $matches = false;
    if(preg_match($_pattern, $_lines, $matches)){
        $description = $matches[1];
    }

    if(empty(strpos($config, '.'))){
        $_line_number = $fileline;

        while($_line_number > 0){
            $_current_line = $lines[--$_line_number];
            // buscando linha comom essa: 'app.apiCache.lifetimeByController' => [
            if(preg_match("#'([\w\d\.]+)' *=> *(\[|array\() *$#", $_current_line, $matches)){
                $config = $matches[1] . ' => ' . $config;
                break;
            }
        }    
    }

    $filename = str_replace(BASE_PATH, '', $filename);

    $description = implode("\n", array_map(function($l) { return trim($l); }, explode("\n", $description)));

    $doc = "\n\n\n## $config";
    $doc .= $description ? "\n{$description}": '';
    $doc .= "\n\n - definível pela variável de ambiente **{$name}**";
    $doc .= $default ? "\n - o valor padrão é `{$default}`" : '';
    $doc .= "\n - definido em `{$filename}:$fileline`";
    
    echo "$doc";
}


if (env('MAPAS_HTTPS', false) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/2.0';
    $_SERVER['REQUEST_SCHEME'] = 'https';
}

