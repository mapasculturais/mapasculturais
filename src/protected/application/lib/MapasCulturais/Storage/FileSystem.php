<?php
namespace MapasCulturais\Storage;

use MapasCulturais\App;

/**
 * Store the files in the filesystem.
 *
 * By default this component stores the files at BASE_PATH . '/files'.
 */
class FileSystem extends \MapasCulturais\Storage{

    /**
     * The FileSystem Sotarage configuration.
     * @var array
     */
    private $_config = [];

    /**
     * Creates the FileSystem Storage component.
     *
     * <code>
     * /**
     *  * Sample Configuration (optional)
     *  * In below example the files will be accessible at url http://mapasculturais.domain/relative/url/
     *  {@*}
     *  new \MapasCulturais\Storage\FileSystem(array(
     *      'dir' => '/full/path/',
     *      'baseUrl' => '/relative/url/'
     *  ))
     * </code>
     *
     * @param array $config
     */
    protected function __construct(array $config = []) {
        $this->config = $config + [
            'dir' => BASE_PATH . 'files/',
            'private_dir' => dirname(BASE_PATH) . '/private-files/',
            'baseUrl' => 'files/'
        ];
    }

    /**
     * Adds the file to the filesystem.
     *
     * @param \MapasCulturais\Entities\File $file
     *
     * @return bool true if the file was added, false otherwise.
     */
    protected function _add(\MapasCulturais\Entities\File $file) {
        if($file->tmpFile['error'] === UPLOAD_ERR_OK){
            $filename = $this->getPath($file);

            if(!is_dir(dirname($filename)))
                mkdir (dirname($filename), 0755, true);

            // if filename exists, add a number before the last dot
            if(file_exists($filename)){
                $original_file_name = $file->name;
                $fcount = 2;
                while(file_exists($filename)){
                    $file->name = preg_replace("#(\.[[:alnum:]]+)$#i", '-' . $fcount . '$1', $original_file_name);
                    $filename = $this->getPath($file);
                    $fcount++;
                }
            }

            rename($file->tmpFile['tmp_name'], $filename);
            chmod($filename, 0666);
        }else{
            return false;
        }
    }

    /**
     * Removes the file from filesystem.
     *
     * @param \MapasCulturais\Entities\File $file
     *
     * @return bool true if the file was removed, false otherwise
     */
    protected function _remove(\MapasCulturais\Entities\File $file) {
        $filename = $this->getPath($file);
        $removed = file_exists($filename) ? unlink($filename) : false;

        // if the folder is empty remove it
        $dir = dirname($filename);
        if($removed && is_readable($dir) && count(scandir($dir)) == 2)
            rmdir($dir);

        return $removed;
    }

    /**
     * Returns the URL to the file.
     *
     * @param \MapasCulturais\Entities\File $file
     *
     * @return string The URL to the file.
     */
    protected function _getUrl(\MapasCulturais\Entities\File $file) {
        $relative_path = $this->getPath($file, true);
        return $this->getUrlFromRelativePath($relative_path);
    }
    
    
    /**
     * Returns the URL based on a relative path.
     *
     * @param \MapasCulturais\Entities\File $file
     *
     * @return string The URL to the file.
     */
    protected function _getUrlFromRelativePath($relative_path) {
        return App::i()->baseUrl . $this->config['baseUrl'] . $relative_path;
    }
        
    

    /**
     * Returns the path to the file.
     *
     * If the owner of the file is another file, the path will be nested.
     *
     * @param \MapasCulturais\Entities\File $file
     * @param type $relative
     *
     * @return string The path to the file.
     */
    protected function _getPath(\MapasCulturais\Entities\File $file, $relative = false){
        
        
        /** 
         * First, we try to get the path info from the $file object
         * If the file already exists in the filesystem, it should have this information stored in the database
         */ 
        $relative_path = $file->getRelativePath(false);
        
        if($relative && $relative_path){
            return $relative_path;
        }
        
        /**
         * If file path is empty, this file is being created now and we are going to return the path
         */ 
        if(!$relative_path){
            
            $parent = $file->parent ? $file->parent : $file->owner;
            
            if($parent && is_object($parent) && $parent instanceof \MapasCulturais\Entities\File){
                $relative_path = dirname($this->getPath($parent, true)) . '/file/' . $parent->id . '/' . $file->name;;
            }else{
                $relative_path = strtolower(str_replace("MapasCulturais\Entities\\", "" , $parent->getClassName())) . '/' . $parent->id . '/' . $file->name;
            }
        }
        
        if ($relative)
            $result =  $relative_path;
        else
            $result = $file->private ? $this->config['private_dir'] . $relative_path : $this->config['dir'] . $relative_path;
        
        return str_replace('\\', '-', $result);
    }


    public function createZipOfEntityFiles($entity, $fileName = null) {
        if($file = $entity->getFile('zipArchive')){
            $file->delete(true);
        }
        \MapasCulturais\App::i()->em->refresh($entity);
        $files = array_map(function($item){
            return '"'.$this->getPath($item).'"';
        }, $entity->files);


        $strFiles = implode(' ', $files);

        $tmpName = sys_get_temp_dir() . '/' . $entity->id . '-' . uniqid() . '.zip';

        if(!$fileName){
            $fileName = $entity->id . '.zip';
        }

        if(exec('zip -j ' . $tmpName . ' ' . $strFiles)){
            $file_class = $entity->getFileClassName();
            $newFile = new $file_class ([
                'name' => $fileName,
                'type' => 'application/zip',
                'tmp_name' => $tmpName,
                'error' => 0,
                'size' => filesize($tmpName)
            ]);
            $newFile->owner = $entity;
            $newFile->group = 'zipArchive';
            $newFile->save(true);
            return $newFile;
        }else{
            //exception: can't create zipfile
            return null;
        }
    }
}
