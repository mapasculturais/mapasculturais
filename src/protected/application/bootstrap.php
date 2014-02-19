<?php
namespace MapasCulturais;
define('BASE_PATH', realpath(__DIR__.'/../../') . '/');
define('PROTECTED_PATH', realpath(__DIR__.'/../') . '/');
define('APPLICATION_PATH', realpath(__DIR__) . '/');
define('THEMES_PATH', APPLICATION_PATH . 'themes/');
define('ACTIVE_THEME_PATH',  THEMES_PATH . 'active/');

define('AUTOLOAD_TTL', 60 * 5);

require_once __DIR__."/../vendor/autoload.php";

if(function_exists('apc_fetch')){
    $autoloaders = spl_autoload_functions();

    foreach($autoloaders as $loader){
        spl_autoload_unregister($loader);
    }

    spl_autoload_register(function ($class) use ($autoloaders){
        $cache_id = "CLASS::autload($class)";
        if(apc_exists($cache_id)){
            $filepath = \apc_fetch($cache_id);
            require_once $filepath;

            return true;
        }

        if(strpos($class, 'DoctrineProxies\__CG__\MapasCulturais\Entities') === 0){
            $filename = APPLICATION_PATH . str_replace('DoctrineProxies\__CG__\MapasCulturais\Entities\\', 'lib/MapasCulturais/DoctrineProxies/__CG__MapasCulturaisEntities', $class) . '.php';
            if(file_exists($filename)){
                require_once $filename;
                if(function_exists('apc_store')){
                    apc_store($cache_id, $filename, AUTOLOAD_TTL);
                }
                return true;
            }
        }else{
            foreach($autoloaders as $loader){
                $loader($class);
                if(class_exists($class, false)){
                    $reflection = new \ReflectionClass($class);
                    \apc_store($cache_id, $reflection->getFileName(), AUTOLOAD_TTL);
                    return true;
                }
            }
        }

    });
}
$config = include __DIR__.'/conf/config.php';

// create the App instance
$app = App::i()->init($config);

$app->register();