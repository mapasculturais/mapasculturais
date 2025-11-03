<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\File;

trait EntityFilesFunctions {

    public static function getFileClassName(){
        $class = get_called_class();
        return $class::getClassName() . 'File';
    }
    /**
     * Returns the files of this entity.
     *
     * If no group is passed, this method will returns all files grouped by file groups.
     *
     * @return \MapasCulturais\Entities\File[] The array of files.
     */
    function getFiles($group = null){
        $app = App::i();

        if($this instanceof File){
            $files = $this->getChildren();
            $result = [];
            foreach($files as $file){
                $result[substr($file->group,4)] = $file;
            }
        }else{
            $result = \MapasCulturais\Entities\File::sortFilesByGroup($this->__files);
        }

        if($group){
            $registeredGroup = $app->getRegisteredFileGroup($this->controllerId, $group);

            if($registeredGroup && $registeredGroup->unique){
                $result = isset($result[$group]) ? $result[$group] : null;
            }else{
                $result = isset($result[$group]) ? $result[$group] : [];
            }

        }
        return $result;
    }

    /**
     * Returns the first File of a group.
     *
     * @return \MapasCulturais\Entities\File A File.
     */
    function getFile($group){
        if($this->__files){
            foreach($this->__files as $file){
                if($file->group === $group){
                    return $file;
                }
            }

            return null;
        }else{
            return App::i()->repo($this->getFileClassName())->findOneBy([
                'owner' => $this,
                'group' => $group
            ]);
        }
    }

    function makeFilesPrivate(){
        foreach($this->__files as $file){
            $file->makePrivate();
        }
    }

    function makeFilesPublic(){
        foreach($this->__files as $file){
            $file->makePublic();
        }
    }
}