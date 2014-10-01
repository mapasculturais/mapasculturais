<?php
namespace MapasCulturais\Themes\Base;

abstract class AssetManager{
    /**
     *
     * @var type
     */
    protected $_enqueuedScripts = array();

    /**
     *
     * @var type
     */
    protected $_enqueuedStyles = array();


    function enqueueScript($group, $script_name, $script_filename, array $dependences = array()){
        if(!key_exists($group, $this->_enqueuedScripts))
                $this->_enqueuedScripts[$group] = array();

        $this->_enqueuedScripts[$group][$script_name] = array($script_name, $script_filename, $dependences);
    }

    function enqueueStyle($group, $style_name, $style_filename, array $dependences = array(), $media = 'all'){
        if(!key_exists($group, $this->_enqueuedStyles))
                $this->_enqueuedStyles[$group] = array();

        $this->_enqueuedStyles[$group][$style_name] = array($style_name, $style_filename, $dependences, $media);
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
                    throw new \Exception(sprintf(App::txt('Missing script dependence: %s depends on %s'), $asset_name, $dep));
            }
            $array[] = $asset_filename;
        }
    }

    protected function _getOrderedScripts($group){
        $result = array();
        if(isset($this->_enqueuedScripts[$group])){
            foreach($this->_enqueuedScripts[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedScripts[$group], $asset, $result);

        }

        return $result;
    }

    protected function _getOrderedStyles($group){
        $result = array();
        if(isset($this->_enqueuedStyles[$group])){
            foreach($this->_enqueuedStyles[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedStyles[$group], $asset, $result);

        }

        return $result;
    }

    function printScripts($group){
        $asset_url = \MapasCulturais\App::i()->getAssetUrl();

        $files = $this->_publishScripts($group);

        $scripts = '';

        foreach ($files as $source){
            if(!preg_match('#^http://|https://|//#', $source))
                $url = $asset_url . $source;
            $scripts .= "\n <script type='text/javascript' src='{$url}'></script>";
        }

        echo $scripts;

    }

    function printStyles($group){
        $asset_url = \MapasCulturais\App::i()->getAssetUrl();

        $files = $this->_publishStyles($group);
        $styles = '';
        foreach ($files as $source){
            if(!preg_match('#^http://|https://|//#', $source))
                $url = $asset_url . $source;
            $styles .= "\n <link href='{$url}' media='all' rel='stylesheet' type='text/css' />";
        }

        echo $styles;
    }

    function assetUrl($asset, $print = true){
        $asset_url = $this->_publishAsset($asset);
        if($print)
            echo $asset_url;
        else
            return $asset_url;
    }

    protected function _publishAsset($asset){
        // copia o asset para a pasta public
    }

    abstract protected function _publishScripts($group);

    abstract protected function _publishStyles($group);
}