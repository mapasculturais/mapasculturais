<?php
namespace MapasCulturais\AssetManagers;

use MapasCulturais\App;

class Assetic extends \MapasCulturais\AssetManager{

    public function __construct(array $config = array()) {

        parent::__construct(array_merge(array(
            'mergeScripts' => false,
            'mergeStyles' => false,
            'filters.styles' => array(),
            'filters.scripts' => array()
        ), $config));
    }

    protected function _createAssetCollection($assets){
        $result = array();

        foreach($assets as $asset)
            $this->_addAssetToArray($assets, $asset, $result);

        $collection = new \Assetic\Asset\AssetCollection();

        foreach($result as $filename)
            $collection->add (new \Assetic\Asset\FileAsset( THEMES_PATH . 'active/assets/' . $filename) );


        return $collection;
    }

    protected function _publishAsset($asset_filename) {
        $app = App::i();

        $asset_filename = $app->view->getAssetFilename($asset_filename);

        if($app->config['app.mode'] === 'development'){
            return str_replace(BASE_PATH, $app->baseUrl, $asset_filename);
        }else{
            $fname = $this->_getPublishedAssetFilename($asset_filename);
            
            return $app->assetUrl . $fname;
        }
    }

    protected function _publishScripts($group) {
        if($this->_config['mergeScripts']){

        }else{

        }
    }

    protected function _publishStyles($group) {
        if($this->_config['mergeStyles']){

        }else{

        }
    }
}