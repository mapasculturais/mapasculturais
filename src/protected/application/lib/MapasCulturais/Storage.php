<?php
namespace MapasCulturais;

/**
 * Abstract File Storage.
 *
 * The file storage is responsible to store files and give url to the files.
 *
 * This component is accessible at App::i()->storage
 *
 * @hook **storage.add:before** *($file)*
 * @hook **storage.add({$owner_entity}):before** *($file)*
 * @hook **storage.add({$owner_entity}:{$file_group}):before** *($file)*
 * @hook **storage.add:after** *($file, &$result)*
 * @hook **storage.add({$owner_entity}):after** *($file, &$result)*
 * @hook **storage.add({$owner_entity}:{$file_group}):after** *($file, &$result)*
 * @hook **storage.remove:before** *($file)*
 * @hook **storage.remove({$owner_entity}):before** *($file)*
 * @hook **storage.remove({$owner_entity}:{$file_group}):before** *($file)*
 * @hook **storage.remove:after** *($file, &$result)*
 * @hook **storage.remove({$owner_entity}):after** *($file, &$result)*
 * @hook **storage.remove({$owner_entity}:{$file_group}):after** *($file, &$result)*
 * @hook **storage.url** *($file, &$path)*
 * @hook **storage.url({$owner_entity})** *($file, &$path)*
 * @hook **storage.url({$owner_entity}:{$file_group})** *($file, &$path)*
 * @hook **storage.path** *($file, &$path)*
 * @hook **storage.path({$owner_entity})** *($file, &$path)*
 * @hook **storage.path({$owner_entity}:{$file_group})** *($file, &$path)*
 */
abstract class Storage{
    use Traits\Singleton;

    /**
     * Stores the file.
     *
     * @param \MapasCulturais\Entities\File $file The file to be stored
     *
     * @hook **storage.add:before** *($file)*
     * @hook **storage.add({$owner_entity}):before** *($file)*
     * @hook **storage.add({$owner_entity}:{$file_group}):before** *($file)*
     * @hook **storage.add:after** *($file, &$result)*
     * @hook **storage.add({$owner_entity}):after** *($file, &$result)*
     * @hook **storage.add({$owner_entity}:{$file_group}):after** *($file, &$result)*
     */
    public function add(\MapasCulturais\Entities\File $file){
        $app = App::i();

        $owner = $file->getOwner();

        $app->applyHookBoundTo($this, 'storage.add:before', ['file' => $file]);
        if($owner){
            $app->applyHookBoundTo($this, 'storage.add(' . $owner->getHookClassPath() . ':' . $file->group . '):before', ['file' => $file]);
        }

        $result = $this->_add($file);

        $app->applyHookBoundTo($this, 'storage.add:after', ['file' => $file, 'result' => &$result]);
        if($owner){
            $app->applyHookBoundTo($this, 'storage.add(' . $owner->getHookClassPath() . ':' . $file->group . '):after', ['file' => $file, 'result' => &$result]);
        }

        return $result;
    }


    /**
     * Removes the file from storage.
     *
     * @param \MapasCulturais\Entities\File $file The file to be removed
     *
     * @hook **storage.remove:before** *($file)*
     * @hook **storage.remove({$owner_entity}):before** *($file)*
     * @hook **storage.remove({$owner_entity}:{$file_group}):before** *($file)*
     * @hook **storage.remove:after** *($file, &$result)*
     * @hook **storage.remove({$owner_entity}):after** *($file, &$result)*
     * @hook **storage.remove({$owner_entity}:{$file_group}):after** *($file, &$result)*
     */
    public function remove(\MapasCulturais\Entities\File $file){
        $app = App::i();

        $owner = $file->getOwner();

        $app->applyHookBoundTo($this, 'storage.remove:before', ['file' => $file]);
        if($owner){
            $app->applyHookBoundTo($this, 'storage.remove(' . $owner->getHookClassPath() . ':' . $file->group . '):before', ['file' => $file]);
        }

        $result = $this->_remove($file);

        $app->applyHookBoundTo($this, 'storage.remove:after', ['file' => $file, 'result' => &$result]);
        if($owner){
            $app->applyHookBoundTo($this, 'storage.remove(' . $owner->getHookClassPath() . ':' . $file->group . '):after', ['file' => $file, 'result' => &$result]);
        }
        return $result;
    }


    /**
     * Returns the url to the file.
     *
     * @param \MapasCulturais\Entities\File $file The file to get the url.
     *
     * @hook **storage.url ($file, &$path)**
     * @hook **storage.url({$owner_entity}) ($file, &$path)**
     * @hook **storage.url({$owner_entity}:{$file_group}) ($file, &$path)**
     */
    public function getUrl(\MapasCulturais\Entities\File $file){
        $app = App::i();

        $owner = $file->getOwner();

        $result = $this->_getUrl($file);

        $app->applyHookBoundTo($this, 'storage.url', ['file' => $file, 'url' => &$result]);

        if($owner){
            $app->applyHookBoundTo($this, 'storage.url(' . $owner->getHookClassPath() . ':' . $file->group . ')', ['file' => $file, 'url' => &$result]);
        }
        return $result;
    }

    /**
     * Returns the full path to the file.
     *
     * @param \MapasCulturais\Entities\File $file The file to get the path
     * @param bool $relative Returns the relative path?
     *
     * @hook **storage.path ($file, &$path)**
     * @hook **storage.path({$owner_entity}) ($file, &$path)**
     * @hook **storage.path({$owner_entity}:{$file_group}) ($file, &$path)**
     */
    public function getPath(\MapasCulturais\Entities\File $file, $relative = false){
        $app = App::i();

        $owner = $file->getOwner();

        $result = $this->_getPath($file, $relative);

        $app->applyHookBoundTo($this, 'storage.path', ['file' => $file, 'path' => &$result]);

        if($owner){
            $app->applyHookBoundTo($this, 'storage.path(' . $owner->getHookClassPath() . ':' . $file->group . ')', ['file' => $file, 'path' => &$result]);
        }
        return $result;
    }

    abstract public function createZipOfEntityFiles($entity);

    abstract protected function _add(\MapasCulturais\Entities\File $file);
    abstract protected function _remove(\MapasCulturais\Entities\File $file);
    abstract protected function _getUrl(\MapasCulturais\Entities\File $file);
    abstract protected function _getPath(\MapasCulturais\Entities\File $file, $relative = false);
}
