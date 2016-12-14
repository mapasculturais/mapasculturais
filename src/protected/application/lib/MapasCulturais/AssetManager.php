<?php
namespace MapasCulturais;

abstract class AssetManager{
    /**
     *
     * @var type
     */
    protected $_enqueuedScripts = [];

    /**
     *
     * @var type
     */
    protected $_enqueuedStyles = [];

    protected $_config = [];

    function __construct(array $config = []) {
        $this->_config = $config;
    }


    function enqueueScript($group, $script_name, $script_filename, array $dependences = []){
        if(!key_exists($group, $this->_enqueuedScripts))
                $this->_enqueuedScripts[$group] = [];

        $this->_enqueuedScripts[$group][$script_name] = [$script_name, $script_filename, $dependences];
    }

    function enqueueStyle($group, $style_name, $style_filename, array $dependences = [], $media = 'all'){
        if(!key_exists($group, $this->_enqueuedStyles))
                $this->_enqueuedStyles[$group] = [];

        $this->_enqueuedStyles[$group][$style_name] = [$style_name, $style_filename, $dependences, $media];
    }

    protected function _addAssetToArray($assets, $asset, array &$array){
        $asset_name = $asset[0];
        $asset_filename = $asset[1];
        $asset_dependences = $asset[2];

        if(!in_array($asset_filename, $array)){
            foreach ($asset_dependences as $dep){
                if(key_exists($dep, $assets))
                    $this->_addAssetToArray ($assets, $assets[$dep], $array);
                else
                    throw new \Exception(sprintf(\MapasCulturais\i::__('Dependência de scripts faltando: %s depende de %s'), $asset_name, $dep));
            }
            $array[] = $asset_filename;
        }
    }

    protected function _getOrderedScripts($group){
        $result = [];
        if(isset($this->_enqueuedScripts[$group])){
            foreach($this->_enqueuedScripts[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedScripts[$group], $asset, $result);

        }

        return $result;
    }

    protected function _getOrderedStyles($group){
        $result = [];
        if(isset($this->_enqueuedStyles[$group])){
            foreach($this->_enqueuedStyles[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedStyles[$group], $asset, $result);

        }

        return $result;
    }

    function printScripts($group){
        $app = App::i();

        $url = null;

        if($app->config['app.useAssetsUrlCache']){
            $keys = array_keys($this->_enqueuedScripts[$group]);
            sort($keys);
            $cache_id = "ASSETS_SCRIPTS:$group:" . implode(':', $keys);
            if($app->cache->contains($cache_id)){
                echo $app->cache->fetch($cache_id);
                return;
            }
        }

        $urls = $this->_publishScripts($group);

        $scripts = '';

        foreach ($urls as $url){
            $scripts .= "\n <script type='text/javascript' src='{$url}'></script>";
        }

        if($app->config['app.useAssetsUrlCache'])
            $app->cache->save ($cache_id, $scripts, $app->config['app.assetsUrlCache.lifetime']);

        echo $scripts;

    }

    function printStyles($group){
        $app = App::i();

        if(!isset($this->_enqueuedStyles[$group]))
            return;

        $url = null;

        if($app->config['app.useAssetsUrlCache']){
            $keys = array_keys($this->_enqueuedStyles[$group]);
            sort($keys);
            $cache_id = "ASSETS_STYLES:$group:" . implode(':', $keys);
            if($app->cache->contains($cache_id)){
                echo $app->cache->fetch($cache_id);
                return;
            }
        }

        $urls = $this->_publishStyles($group);

        $styles = '';

        foreach ($urls as $url){
            $styles .= "\n <link href='{$url}' media='all' rel='stylesheet' type='text/css' />";
        }

        if($app->config['app.useAssetsUrlCache'])
            $app->cache->save ($cache_id, $styles, $app->config['app.assetsUrlCache.lifetime']);

        echo $styles;
    }

    function assetUrl($asset){
        $app = App::i();

        $cache_id = "ASSET_URL:$asset";

        if($app->config['app.useAssetsUrlCache'] && $app->cache->contains($cache_id)){
            $asset_url = $app->cache->fetch($cache_id);

        }else{
            $asset_url = $this->publishAsset($asset);

            if($app->config['app.useAssetsUrlCache'])
                $app->cache->save ($cache_id, $asset_url, $app->config['app.assetsUrlCache.lifetime']);

        }

        return $asset_url;

    }

    function _getPublishedAssetFilename($asset_filename){
        $pathinfo = pathinfo($asset_filename);
        $ftime = filemtime($asset_filename);

        if(strtolower($pathinfo['extension']) === 'js' || strtolower($pathinfo['extension']) === 'css')
            return $pathinfo['filename'] . '-' . $ftime . '.' . $pathinfo['extension'];
        else
            return $pathinfo['filename'] . '.' . $pathinfo['extension'];
    }

    function _getPublishedScriptsGroupFilename($group, $content){
        return $group . '-' . md5($content) . '.js';
    }

    function _getPublishedStylesGroupFilename($group, $content){
        return $group . '-' . md5($content) . '.css';
    }
    
    function publishFolder($dir, $destination = null){
        $app = App::i();
        
        $destination = $destination ? $destination : $dir;
        
        $cache_id = __METHOD__ . '::' . $dir . '->' . $destination;
        
        if(!$app->config['app.useAssetsUrlCache'] || !$app->cache->contains($cache_id)){
            foreach ($app->view->path as $path){
                $dirpath = $path . 'assets/' . $dir;
                if(is_dir($dirpath)){
                    $this->_publishFolder($dirpath . '/*', $destination);
                }
            }
                
            if($app->config['app.useAssetsUrlCache']){
                $app->cache->save($cache_id, '1');
            }
        }   
    }

    function publishAsset($asset_filename, $destination = null){
        $app = App::i();

        if(preg_match('#^(\/\/|https?)#', $asset_filename))
            return $asset_filename;

        $asset_filename = $app->view->getAssetFilename($asset_filename);
        
        if(!$asset_filename)
            return '';

        $info = pathinfo($asset_filename);
        
        $extension = strtolower($info['extension']);
        
        if(!$destination){
            $destination_file = $this->_getPublishedAssetFilename($asset_filename);

            if(in_array($extension, ['jpg', 'png', 'gif', 'ico'])){
                $destination = "img/$destination_file";
            }else{
                $destination = "$extension/$destination_file";
            }
        }
        
        $cache_id = __METHOD__ . '::' . $asset_filename . '->' . $destination;
        
        if($app->config['app.useAssetsUrlCache'] && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }else{
            $asset_url = $this->_publishAsset($asset_filename, $destination);
            
            if($app->config['app.useAssetsUrlCache']){
                $app->cache->save($cache_id, $asset_url);
            }
            
            return $asset_url;
        }
    }

    abstract protected function _publishAsset($asset, $destination);
    
    abstract protected function _publishFolder($path, $destination);

    abstract protected function _publishScripts($group);

    abstract protected function _publishStyles($group);
}
