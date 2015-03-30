<?php
namespace MapasCulturais\Traits;

use Doctrine\Common\Collections\Criteria;
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
 *      'avatar' => array( /* Files {@*} ),
 *      'downloads' => array( /* Files {@*} ),
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

        if(!$this->__files->count()){
            return null;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("group", $group))
            ->setFirstResult(0)
            ->setMaxResults(1);

        $file = $this->__files->matching($criteria);

        if($file){
            return $file[0];
        }else{
            return null;
        }
    }


    /**
     * This entity uses files
     * @return bool true
     */
    public function usesFiles(){
        return true;
    }
}