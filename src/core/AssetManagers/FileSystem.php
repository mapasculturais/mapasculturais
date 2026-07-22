<?php
namespace MapasCulturais\AssetManagers;

use MapasCulturais\App;

/**
 * Gerenciador de assets usando sistema de arquivos
 * 
 * Esta classe implementa um gerenciador de assets que armazena arquivos
 * no sistema de arquivos local, permitindo processamento e publicação
 * de scripts e estilos.
 * 
 * @package MapasCulturais\AssetManagers
 */
class FileSystem extends \MapasCulturais\AssetManager{

    /**
     * Construtor do gerenciador de assets FileSystem
     * 
     * @param array $config Configurações do gerenciador
     */
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

    /**
     * Cria o diretório para armazenar o asset
     * 
     * @param string $output_file Caminho do arquivo de saída
     * @param bool $is_dir Se true, cria o diretório especificado em $output_file
     */
    protected function _mkAssetDir($output_file, $is_dir = false){
        if($is_dir){
            $path = $this->config['publishPath'] . $output_file;
        }else{
            $path = dirname($this->config['publishPath'] . $output_file);
        }

        if(!is_dir($path))
            mkdir($path,0777,true);
    }

    /**
     * Executa um comando de processamento de asset
     * 
     * @param string $command_pattern Padrão do comando com placeholders
     * @param string $input_files Arquivos de entrada
     * @param string $output_file Arquivo de saída
     */
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
        
        $result = '';
        $result_code = '';
        exec($command, $result, $result_code);

        $app = App::i();
        if($app->config['app.log.assetManager']) {
            $log = print_r($result, true);
            $app->log->debug(" ASSETMANAGER EXEC:$result_code > $log");
        }
    }
    
    /**
     * Publica um asset individual
     * 
     * @param string $asset_filename Caminho do arquivo do asset
     * @param string $output_file Caminho do arquivo de saída
     * @return string URL do asset publicado
     */
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

    /**
     * Publica uma pasta inteira de assets
     * 
     * @param string $path Caminho da pasta de origem
     * @param string $destination Caminho da pasta de destino
     */
    protected function _publishFolder($path, $destination){
        $this->_mkAssetDir($destination, true);
        $this->_exec($this->config['publishFolderCommand'], $path, $destination);
    }

    /**
     * Publica os scripts de um grupo
     * 
     * @param string $group Nome do grupo de scripts
     * @return array URLs dos scripts publicados
     */
    protected function _publishScripts($group) {
        return $this->_publishAssetGroup('js', $group);
    }

    /**
     * Publica os estilos de um grupo
     * 
     * @param string $group Nome do grupo de estilos
     * @return array URLs dos estilos publicados
     */
    protected function _publishStyles($group) {
        return $this->_publishAssetGroup('css', $group);
    }

    /**
     * Publica um grupo de assets (scripts ou estilos)
     * 
     * @param string $extension Extensão dos assets ('js' ou 'css')
     * @param string $group Nome do grupo
     * @return array URLs dos assets publicados
     */
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