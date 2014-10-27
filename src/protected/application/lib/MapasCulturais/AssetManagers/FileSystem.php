<?php
namespace MapasCulturais\AssetManagers;

use MapasCulturais\App;

class FileSystem extends \MapasCulturais\AssetManager{

    public function __construct(array $config = array()) {

        parent::__construct(array_merge(array(
            'publishPath' => BASE_PATH . 'public/',

            'mergeScripts' => false,
            'mergeStyles' => false,

            'process.js' => '',
            'process.css' => '',

            'publishFolderCommand' => 'ln -s -f {IN} {PUBLISH_PATH}'
        ), $config));

    }

    protected function _mkAssetDir($output_file){
        $path = dirname($this->_config['publishPath'] . $output_file);

        if(!is_dir($path))
            mkdir($path,0777,true);
    }

    protected function _exec($command_pattern, $input_files, $output_file){
        $this->_mkAssetDir($output_file);

        $command = str_replace(array(
                '{IN}',
                '{OUT}',
                '{FILENAME}',
                '{PUBLISH_PATH}'
            ), array(
                $input_files,
                $this->_config['publishPath'] . $output_file,
                $output_file,
                $this->_config['publishPath']
            ), $command_pattern);

        exec($command);
    }
    protected function _publishAsset($asset_filename) {
        $app = App::i();

        if(preg_match('#^(\/\/|https?)#', $asset_filename))
            return $asset_filename;

        $asset_filename = $app->view->getAssetFilename($asset_filename);
        if(!$asset_filename)
            return '';


        $info = pathinfo($asset_filename);
        $extension = strtolower($info['extension']);

        $output_file = $this->_getPublishedAssetFilename($asset_filename);

        if(in_array($extension, array('jpg', 'png', 'gif')))
            $output_file = "img/$output_file";
        else
            $output_file = "$extension/$output_file";

        if(isset($this->_config["process.{$extension}"])){
            $this->_exec($this->_config["process.{$extension}"], $asset_filename, $output_file);
        }else{
            $this->_mkAssetDir($output_file);
            copy($asset_filename, $this->_config['publishPath'] . $output_file);
        }

        return $app->assetUrl . $output_file;
    }

    function publishFolder($dir){
        $app = App::i();
        $cache_id = __METHOD__ . '::' . $dir;
        if(!$app->cache->contains($cache_id)){
            $in = App::i()->view->getAssetFilename($dir);
            $this->_exec($this->_config['publishFolderCommand'], $in, $dir);
            $app->cache->save($cache_id, '1');
        }
    }

    protected function _publishScripts($group) {
        return $this->_publishAssetGroup('js', $group);
    }

    protected function _publishStyles($group) {
        return $this->_publishAssetGroup('css', $group);
    }

    protected function _publishAssetGroup($extension, $group) {
        if($extension === 'js'){
            $enqueuedAssets = isset($this->_enqueuedScripts[$group]) ? $this->_enqueuedScripts[$group] : null;
            $merge = $this->_config['mergeScripts'];
            $process_pattern = $this->_config['process.js'];

            $ordered = $this->_getOrderedScripts($group);
        }else{
            $enqueuedAssets = isset($this->_enqueuedStyles[$group]) ? $this->_enqueuedStyles[$group] : null;
            $merge = $this->_config['mergeStyles'];
            $process_pattern = $this->_config['process.css'];

            $ordered = $this->_getOrderedStyles($group);
        }

        if(!$enqueuedAssets)
            return array();

        $app = App::i();

        $result = array();

        if($merge){
            $theme = $app->view;
            $content = "";

            $assets = array_map(function($e) use($theme, &$content, &$result){
                if(preg_match('#^(\/\/|https?)#', $e)){
                    $result[] = $e;
                    return ;
                }
                $filename = $theme->getAssetFilename($e);
                $content .= file_get_contents($filename)."\n";

                return $filename;
            }, $ordered);

            if($extension === 'js'){
                $output_file = $this->_getPublishedScriptsGroupFilename($group, $content);
            }else{
                $output_file = $this->_getPublishedStylesGroupFilename($group, $content);
            }
            $output_file = "$extension/$output_file";
            if($process_pattern){
                $input_files = implode(' ', $assets);
                $this->_exec($process_pattern, $input_files, $output_file);

            }else{
                file_put_contents($output_file, $content);
            }

            $result[] = $app->assetUrl . $output_file;
        }else{
            foreach($ordered as $asset){
                $result[] = $this->_publishAsset($asset);
            }
        }
        return $result;
    }
}