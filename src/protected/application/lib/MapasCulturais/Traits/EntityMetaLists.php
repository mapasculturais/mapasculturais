<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Defines that the entity has metalist.
 *
 * @property-read array $metaLists array of metalists grouped by the metalist group
 */
trait EntityMetaLists{

    /**
     * This entity has metalists
     * @return bool true
     */
    public function usesMetaLists(){
        return true;
    }

    /**
     * Returns the metalists of this entity.
     *
     * If a group name is informed returns only the metalists of the given group, otherwise returns all metalists
     * of this entity grouped by the group name.
     *
     * <code>
     *  // Example of return when no group is informed
     *  array(
     *      'links' => array($link1, $link2),
     *      'videos' => array($video1, $video2, $video3)
     *  )
     * </code>
     *
     */
    function getMetaLists($group = null){
        $app = App::i();

        $cache_id = "{$this->className}:{$this->id}:" . __FUNCTION__ . ($group ? "({$group})" : '');

        if($app->objectCacheEnabled() && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        if($group){
            $result = $app->repo('MetaList')->findByGroup($this, $group);
        }else{
            $result = $app->repo('MetaList')->findByOwnerGroupedByGroup($this);
        }
        $app->cache->save($cache_id, $result, $app->objectCacheTimeout());
        return $result;
    }

    function clearMetalistsCache(){
        $app = App::i();
        $class_name = $this->className;

        $groups = $app->getRegisteredMetaListGroupsByEntity($this);
        $app->cache->delete("{$class_name}:{$this->id}:getMetaLists");
        foreach($groups as $g){
            $app->cache->delete("{$class_name}:{$this->id}:getMetaLists({$g->name})");
        }
    }
}