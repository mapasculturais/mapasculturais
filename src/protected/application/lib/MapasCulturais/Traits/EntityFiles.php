<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\File;

/**
 * Defines that the entity has files.
 *
 * Use this trait in entities that has files. The file groups must be registered.
 *
 * <code>
 * // example of $entity->files
 * array(
 *      'avatar' => [ /* Files {@*} ],
 *      'downloads' => [ /* Files {@*} ],
 * )
 * </code>
 *
 * @property-read \MapasCulturais\Entities\File[] $files Files of this entities grouped by file groups.
 *
 * @see \MapasCulturais\Definitions\FileGroup
 * @see \MapasCulturais\App::registerFileGroup()
 */
trait EntityFiles{

    function getFileClassName(){
        return $this->getClassName() . 'File';
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


    /**
     * This entity uses files
     * @return bool true
     */
    public static function usesFiles(){
        return true;
    }
}