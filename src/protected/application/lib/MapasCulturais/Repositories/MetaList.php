<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

class MetaList extends \MapasCulturais\Repository{
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findBy(array('objectType' => $owner->getClassName(), 'objectId' => $owner->id, 'group' => $group), array('id'=>'ASC'));
        
        return $result;
    }

    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findOneBy(array('objectType' => $owner->getClassName(), 'objectId' => $owner->id, 'group' => $group));

        return $result;
    }

    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){

        $metalists = $this->findBy(array('objectId' => $owner->id, 'objectType' =>  $owner->getClassName()));

        $result = array();

        if($metalists){
            foreach($metalists as $metalist){
                if(!key_exists($metalist->group, $result))
                    $result[trim($metalist->group)] = array();

                $result[trim($metalist->group)][] = $metalist;
            }
        }

        ksort($result);

        return $result;
    }
}