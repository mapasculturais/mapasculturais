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

        $cache_id = "{$this->className}:{$this->id}:" . __FUNCTION__ . ($group ? "({$group})" : '');

        if($app->objectCacheEnabled() && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        if($group)
            $result = $app->repo('File')->findByGroup($this, $group);
        else
            $result = $app->repo('File')->findByOwnerGroupedByGroup($this);

        if($app->objectCacheEnabled())
            $app->cache->save($cache_id, $result, $app->objectCacheTimeout());

        return $result;
    }

    /**
     * Returns the first File of a group.
     *
     * @return \MapasCulturais\Entities\File A File.
     */
    function getFile($group){
        $app = App::i();

        $cache_id = "{$this->className}:{$this->id}:" . __FUNCTION__ . ($group ? "({$group})" : '');

        if($app->cache->contains($cache_id))
            return $app->cache->fetch($cache_id);

        $result = $app->repo('File')->findOneByGroup($this, $group);

        if($app->objectCacheEnabled())
            $app->cache->save($cache_id, $result, $app->objectCacheTimeout());

        return $result;
    }

    function clearFilesCache(){
        $app = App::i();
        $class_name = $this->className;

        $app->cache->delete("{$class_name}:{$this->id}:getFile");
        $app->cache->delete("{$class_name}:{$this->id}:getFiles");

        $groups = $app->getRegisteredFileGroupsByEntity($this);

        foreach($groups as $g){
            $app->cache->delete("{$class_name}:{$this->id}:getFile({$g->name})");
            $app->cache->delete("{$class_name}:{$this->id}:getFiles({$g->name})");
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