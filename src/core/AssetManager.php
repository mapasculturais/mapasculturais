<?php
namespace MapasCulturais;

/**
 * Classe abstrata base para gerenciamento de assets (scripts, estilos, imagens, etc.)
 * 
 * @property array $config Configurações do gerenciador de assets
 * 
 * @package MapasCulturais
 */
abstract class AssetManager{

    /**
     * Scripts enfileirados por grupo
     * @var array
     */
    protected $_enqueuedScripts = [];

    /**
     * Estilos enfileirados por grupo
     * @var array
     */
    protected $_enqueuedStyles = [];

    /**
     * Configurações do gerenciador de assets
     * @var array
     */
    public $config = [];

    /**
     * Construtor da classe
     * 
     * @param array $config
     */
    function __construct(array $config = []) {
        $this->config = $config;
    }


    /**
     * Enfileira um script
     * 
     * @param string $group grupo do script (ex: 'main', 'footer')
     * @param string $script_name nome único do script
     * @param string $script_filename caminho do arquivo
     * @param array $dependences lista de nomes de scripts dependentes
     * @return void
     */
    function enqueueScript($group, $script_name, $script_filename, array $dependences = []){
        if(!key_exists($group, $this->_enqueuedScripts))
                $this->_enqueuedScripts[$group] = [];

        $this->_enqueuedScripts[$group][$script_name] = [$script_name, $script_filename, $dependences];
    }

    /**
     * Enfileira um estilo (CSS)
     * 
     * @param string $group grupo do estilo
     * @param string $style_name nome único do estilo
     * @param string $style_filename caminho do arquivo
     * @param array $dependences lista de nomes de estilos dependentes
     * @param string $media atributo media do link (padrão: 'all')
     * @return void
     */
    function enqueueStyle($group, $style_name, $style_filename, array $dependences = [], $media = 'all'){
        if(!key_exists($group, $this->_enqueuedStyles))
                $this->_enqueuedStyles[$group] = [];

        $this->_enqueuedStyles[$group][$style_name] = [$style_name, $style_filename, $dependences, $media];
    }

    /**
     * Adiciona um asset a um array respeitando as dependências (recursivo)
     * 
     * @param array $assets lista de assets disponíveis
     * @param array $asset asset a ser adicionado
     * @param array $array array de destino (referência)
     * @return void
     * @throws \Exception caso uma dependência não seja encontrada
     */
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

    /**
     * Retorna os scripts de um grupo ordenados por dependência
     * 
     * @param string $group
     * @return array
     */
    protected function _getOrderedScripts($group){
        $result = [];
        if(isset($this->_enqueuedScripts[$group])){
            foreach($this->_enqueuedScripts[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedScripts[$group], $asset, $result);

        }

        return $result;
    }

    /**
     * Retorna os estilos de um grupo ordenados por dependência
     * 
     * @param string $group
     * @return array
     */
    protected function _getOrderedStyles($group){
        $result = [];
        if(isset($this->_enqueuedStyles[$group])){
            foreach($this->_enqueuedStyles[$group] as $asset)
                $this->_addAssetToArray($this->_enqueuedStyles[$group], $asset, $result);

        }

        return $result;
    }

    /**
     * Imprime as tags <script> para um grupo de scripts
     * 
     * @param string $group
     * @return void
     */
    function printScripts($group){
        $app = App::i();

        $url = null;

        if($app->config['app.useAssetsUrlCache']){
            $keys = array_keys($this->_enqueuedScripts[$group] ?? []);
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
            $app->cache->save ($cache_id, $scripts, (int) $app->config['app.assetsUrlCache.lifetime']);

        echo $scripts;

    }

    /**
     * Imprime as tags <link> para um grupo de estilos
     * 
     * @param string $group
     * @return void
     */
    function printStyles($group){
        $app = App::i();

        if(!isset($this->_enqueuedStyles[$group]))
            return;

        $url = null;

        if($app->config['app.useAssetsUrlCache']){
            $keys = array_keys($this->_enqueuedStyles[$group] ?? []);
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
            $app->cache->save ($cache_id, $styles, (int) $app->config['app.assetsUrlCache.lifetime']);

        echo $styles;
    }

    /**
     * Retorna a URL de um asset publicando-o se necessário
     * 
     * @param string $asset caminho do arquivo de origem
     * @param bool $include_hash_in_filename se deve incluir o hash no nome do arquivo publicado
     * @return string
     */
    function assetUrl($asset, $include_hash_in_filename = true){
        $app = App::i();

        $cache_id = "ASSET_URL:$asset:$include_hash_in_filename";

        if($app->config['app.useAssetsUrlCache'] && $app->cache->contains($cache_id)){
            $asset_url = $app->cache->fetch($cache_id);

        }else{
            $asset_url = $this->publishAsset($asset, null, $include_hash_in_filename);

            if($app->config['app.useAssetsUrlCache'])
                $app->cache->save ($cache_id, $asset_url, (int) $app->config['app.assetsUrlCache.lifetime']);

        }

        return $asset_url;

    }

    /**
     * Prefixo para os nomes de arquivos publicados
     * @var string|null
     */
    private $_filenamePrefix = null;

    /**
     * Retorna o prefixo para os nomes de arquivos publicados (gerado uma vez por cache)
     * 
     * @return string
     */
    function getFilenamePrefix() {
        $app = App::i();
        if($this->_filenamePrefix) {
            return $this->_filenamePrefix;
        }

        if($app->cache->contains(__METHOD__)) {
            $this->_filenamePrefix = $app->cache->fetch(__METHOD__);
        } else {
            $this->_filenamePrefix = uniqid();
            $app->cache->save(__METHOD__, $this->_filenamePrefix);
        }

        return $this->_filenamePrefix;
    }

    /**
     * Gera o nome do arquivo publicado para um asset
     * 
     * @param string $asset_filename
     * @param bool $include_hash_in_filename
     * @return string
     */
    function _getPublishedAssetFilename($asset_filename, $include_hash_in_filename = true){
        $pathinfo = pathinfo($asset_filename);
        $ftime = filemtime($asset_filename);
        $hash = base_convert(crc32($asset_filename . $ftime . $this->getFilenamePrefix()), 10, 36);

        $folder_name = basename($pathinfo['dirname']);

        if ($include_hash_in_filename) {
            return "{$pathinfo['filename']}.{$folder_name}.{$hash}.{$pathinfo['extension']}";
        } else {
            return "{$pathinfo['filename']}.{$folder_name}.{$pathinfo['extension']}";
        }
    }

    /**
     * Gera o nome do arquivo para um grupo de scripts publicados
     * 
     * @param string $group
     * @param string $content
     * @return string
     */
    function _getPublishedScriptsGroupFilename($group, $content){
        $hash = base_convert(crc32($content . $this->getFilenamePrefix()),10,36);
        return "{$group}.{$hash}.js";
    }

    /**
     * Gera o nome do arquivo para um grupo de estilos publicados
     * 
     * @param string $group
     * @param string $content
     * @return string
     */
    function _getPublishedStylesGroupFilename($group, $content){
        $hash = base_convert(crc32($content . $this->getFilenamePrefix()),10,36);
        return "{$group}.{$hash}.css";
    }
    
    /**
     * Publica uma pasta de assets
     * 
     * @param string $dir pasta de origem
     * @param string|null $destination pasta de destino
     * @return void
     */
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

    /**
     * Publica um asset individualmente
     * 
     * @param string $asset_filename arquivo de origem
     * @param string|null $destination caminho de destino
     * @param bool $include_hash_in_filename
     * @return string URL do asset publicado
     */
    function publishAsset($asset_filename, $destination = null, $include_hash_in_filename = true){
        $app = App::i();
        if(preg_match('#^(\/\/|https?)#', $asset_filename))
            return $asset_filename;

        $asset_filename = $app->view->getAssetFilename($asset_filename);
        
        if(!$asset_filename)
            return '';

        $info = pathinfo($asset_filename);
        
        $extension = strtolower($info['extension']);
        
        if(!$destination){
            $destination_file = $this->_getPublishedAssetFilename($asset_filename, $include_hash_in_filename);

            if(in_array($extension, ['jpg', 'png', 'gif', 'ico'])){
                $destination = "img/$destination_file";
            }else{
                $destination = "$extension/$destination_file";
            }
        }
        
        $cache_id = __METHOD__ . '::' . $asset_filename . '->' . $destination;
        if($app->config['app.useAssetsUrlCache'] && $app->cache->contains($cache_id)){
            $result = $app->cache->fetch($cache_id);
        }else{
            $asset_url = $this->_publishAsset($asset_filename, $destination);
            
            if($app->config['app.useAssetsUrlCache']){
                $app->cache->save($cache_id, $asset_url);
            }
            
            $result = $asset_url;
        }

        return $result;
    }

    /**
     * Publica um asset no destino (implementação dependente do driver)
     * 
     * @param string $asset
     * @param string $destination
     */
    abstract protected function _publishAsset($asset, $destination);
    
    /**
     * Publica uma pasta no destino (implementação dependente do driver)
     * 
     * @param string $path
     * @param string $destination
     */
    abstract protected function _publishFolder($path, $destination);

    /**
     * Publica os scripts de um grupo (implementação dependente do driver)
     * 
     * @param string $group
     */
    abstract protected function _publishScripts($group);

    /**
     * Publica os estilos de um grupo (implementação dependente do driver)
     * 
     * @param string $group
     */
    abstract protected function _publishStyles($group);
}
