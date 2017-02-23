<?php
namespace MapasCulturais\AssetManagers;

use MapasCulturais\App;

class FileSystem extends \MapasCulturais\AssetManager{

    public function __construct(array $config = []) {

        parent::__construct(array_merge([
            'publishPath' => BASE_PATH . 'public/',

            'mergeScripts' => false,
            'mergeStyles' => false,

            'process.js' => '',
            'process.css' => '',

            'publishFolderCommand' => 'ln -s -f {IN} {PUBLISH_PATH}'
        ], $config));

    }

    protected function _mkAssetDir($output_file, $is_dir = false){
        if($is_dir){
            $path = $this->config['publishPath'] . $output_file;
        }else{
            $path = dirname($this->config['publishPath'] . $output_file);
        }

        if(!is_dir($path))
            mkdir($path,0777,true);
    }

    protected function _exec($command_pattern, $input_files, $output_file){
        $this->_mkAssetDir($output_file);
        

        $command = str_replace([
                '{IN}',
                '{OUT}',
                '{FILENAME}',
                '{PUBLISH_PATH}'
            ], [
                $input_files,
                $this->config['publishPath'] . $output_file,
                $output_file,
                $this->config['publishPath']
            ], $command_pattern);

        
        if($command_pattern === 'cp -Rf {IN} {PUBLISH_PATH}')
            die(var_dump($command));
            
        exec($command);
    }
    
    protected function _publishAsset($asset_filename, $output_file) {
        $app = App::i();

        $info = pathinfo($asset_filename);
        $extension = strtolower($info['extension']);

        if(isset($this->config["process.{$extension}"])){
            $this->_exec($this->config["process.{$extension}"], $asset_filename, $output_file);
        }else{
            $this->_mkAssetDir($output_file);
            copy($asset_filename, $this->config['publishPath'] . $output_file);
        }

        return $app->assetUrl . $output_file;
    }

    protected function _publishFolder($path, $destination){
        $this->_mkAssetDir($destination, true);
        $this->_exec($this->config['publishFolderCommand'], $path, $destination);
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
            $merge = $this->config['mergeScripts'];
            $process_pattern = $this->config['process.js'];

            $ordered = $this->_getOrderedScripts($group);
        }else{
            $enqueuedAssets = isset($this->_enqueuedStyles[$group]) ? $this->_enqueuedStyles[$group] : null;
            $merge = $this->config['mergeStyles'];
            $process_pattern = $this->config['process.css'];

            $ordered = $this->_getOrderedStyles($group);
        }

        if(!$enqueuedAssets)
            return [];

        $app = App::i();

        $result = [];

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
                $result[] = $this->publishAsset($asset);
            }
        }
        return $result;
    }
}