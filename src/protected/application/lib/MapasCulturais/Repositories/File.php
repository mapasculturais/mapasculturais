<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

class File extends \MapasCulturais\Repository{
    use \MapasCulturais\Traits\RepositoryCache;
    
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $result = $this->findBy(array('objectType' => $owner->className, 'objectId' => $owner->id, 'group' => $group));

        $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $group);

        if($result && (($registeredGroup && $registeredGroup->unique) || $app->getRegisteredImageTransformation($group) || (!$registeredGroup && !$app->getRegisteredImageTransformation($group))))
            $result = $result[0];


        return $result;
    }

    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $result = $this->findOneBy(array('objectType' => $owner->className, 'objectId' => $owner->id, 'group' => $group));

        return $result;
    }

    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){
        $app = App::i();
        $files = $this->findBy(array('objectId' => $owner->id, 'objectType' =>  $owner->getClassName()));
        $result = array();

        if($files){
            foreach($files as $file){
                $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $file->group);
                if($registeredGroup && $registeredGroup->unique || $app->getRegisteredImageTransformation($file->group) || (!$registeredGroup && !$app->getRegisteredImageTransformation($file->group))){
                    $result[trim($file->group)] = $file;
                }else{
                    if(!key_exists($file->group, $result))
                        $result[trim($file->group)] = array();

                    $result[trim($file->group)][] = $file;
                }
            }
        }

        ksort($result);

        return $result;
    }
}