<?php
namespace MapasCulturais\AssetManagers;

class Assetic extends \MapasCulturais\AssetManager{
    
    protected function _createAssetCollection($assets){
        $result = array();

        foreach($assets as $asset)
            $this->_addAssetToArray($assets, $asset, $result);

        $collection = new \Assetic\Asset\AssetCollection();

        foreach($result as $filename)
            $collection->add (new \Assetic\Asset\FileAsset( THEMES_PATH . 'active/assets/' . $filename) );


        return $collection;
    }

    protected function _publishAsset($asset) {
        ;
    }

    protected function _publishScripts($group) {
        ;
    }

    protected function _publishStyles($group) {
        ;
    }
}