<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

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
 * @property-read $files Files of this entities grouped by file groups.
 *
 * @see \MapasCulturais\Definitions\FileGroup
 * @see \MapasCulturais\App::registerFileGroup()
 */
trait EntityFiles{
    /**
     * Returns the files of this entity.
     *
     * If no group is passed, this method will returns all files grouped by file groups.
     *
     * @return \MapasCulturais\Entities\File[] The array of files.
     */
    function getFiles($group = null){
        $app = App::i();

        if($group)
            $result = $app->repo('File')->findByGroup($this, $group);
        else
            $result = $app->repo('File')->findByOwnerGroupedByGroup($this);

        return $result;
    }

    /**
     * Returns the first File of a group.
     *
     * @return \MapasCulturais\Entities\File A File.
     */
    function getFile($group){
        $app = App::i();

        $result = $app->repo('File')->findOneByGroup($this, $group);

        return $result;
    }

    /**
     * This entity uses files
     * @return bool true
     */
    public function usesFiles(){
        return true;
    }
}