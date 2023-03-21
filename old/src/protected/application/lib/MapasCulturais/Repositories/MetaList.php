<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

class MetaList extends \MapasCulturais\Repository{
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findBy([
            'objectType' => $owner->getClassName(), 
            'objectId' => $owner->id, 
            'group' => $group
        ], ['id'=>'ASC']);
        
        return $result;
    }

    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findOneBy([
            'objectType' => $owner->getClassName(), 
            'objectId' => $owner->id, 
            'group' => $group
        ]);

        return $result;
    }

    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){

        $metalists = $this->findBy([
            'objectId' => $owner->id, 
            'objectType' =>  $owner->getClassName()
        ]);

        $result = [];

        if($metalists){
            foreach($metalists as $metalist){
                if(!key_exists($metalist->group, $result))
                    $result[trim($metalist->group)] = [];

                $result[trim($metalist->group)][] = $metalist;
            }
        }

        ksort($result);

        return $result;
    }
}