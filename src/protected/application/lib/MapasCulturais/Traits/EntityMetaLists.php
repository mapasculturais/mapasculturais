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
    public static function usesMetaLists(){
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
     *      'links' => [$link1, $link2],
     *      'videos' => [$video1, $video2, $video3]
     *  )
     * </code>
     *
     */
    function getMetaLists($group = null){
        $app = App::i();

        if($group){
            $result = $app->repo('MetaList')->findByGroup($this, $group);
        }else{
            $result = $app->repo('MetaList')->findByOwnerGroupedByGroup($this);
        }

        return $result;
    }
}