<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

/**
 * Classe base para módulos do MapasCulturais
 * 
 * @property-read array $config Array de configuração do módulo
 * 
 * @package MapasCulturais
 */
abstract class Module {
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\RegisterFunctions;
    
    /**
     * Configuração do módulo
     * @var array
     */
    protected $_config;
    
    /**
     * Inicialização do módulo (método abstrato)
     */
    abstract function _init();
    
    /**
     * Registro do módulo (método abstrato)
     */
    abstract function register();

    /**
     * Indica se o módulo é um plugin
     * 
     * @return bool
     */
    static function isPlugin() {
        return false;
    }
    
    /**
     * Construtor do módulo
     * 
     * @param array $config Configuração do módulo
     */
    function __construct(array $config = []) {
        $this->_config = $config;
        
        $app = App::i();
        $active_theme = $app->view;
        $class = get_called_class();

        $priority = $class::isPlugin() ? 50 : 200;
        
        $app->hook('mapasculturais.init', function() use($class, $active_theme){
            $reflaction = new \ReflectionClass($class);
        
            while($reflaction->getName() != __CLASS__){
                $dir = dirname($reflaction->getFileName());
    
                if($dir != __DIR__) {
                    $active_theme->addPath($dir);
                }
                
                $reflaction = $reflaction->getParentClass();
            }
        }, $priority);
        

        // include Module Entities path to doctrine path
        $path = self::getPath('Entities');
        if(is_dir($path)) {
            $driver = $app->em->getConfiguration()->getMetadataDriverImpl();
            $driver->addPaths([$path]);
        }
        
        $app->applyHookBoundTo($this, "module({$class}).init:before");
        $this->_init();
        $app->applyHookBoundTo($this, "module({$class}).init:after");
    }
    
    /**
     * Retorna a configuração do módulo
     * 
     * @return array
     */
    function getConfig(){
        return $this->_config;
    }

    /**
     * Retorna o caminho do diretório do módulo
     * 
     * @param string $subfolder Subdiretório opcional
     * @return string
     */
    static function getPath($subfolder = '') {
        $app = App::i();

        $called_class = get_called_class();

        $cache_key = "{$called_class}:path";
        if ($app->cache->contains($cache_key)) {
            $path = $app->cache->fetch($cache_key);
        } else {
            $reflector = new \ReflectionClass($called_class);
            $path = dirname($reflector->getFileName()) . '/';

            $app->cache->save($cache_key, $path);
        }

        return $subfolder ? "{$path}{$subfolder}/" : $path;
    }
}