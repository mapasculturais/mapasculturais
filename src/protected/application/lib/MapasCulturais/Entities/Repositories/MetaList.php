<?php
namespace MapasCulturais\Entities\Repositories;

use Doctrine\ORM\EntityRepository;
use MapasCulturais\Entities\MetaList as Entity;
use MapasCulturais\App;

class MetaList extends EntityRepository{
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $result = $this->findBy(array('objectType' => $owner->getClassName(), 'objectId' => $owner->id, 'group' => $group), array('id'=>'ASC'));
        //$this->_em->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
        return $result;
    }

    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

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