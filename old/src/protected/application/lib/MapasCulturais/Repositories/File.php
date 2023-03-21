<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

class File extends \MapasCulturais\Repository{

    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $result = $repo->findBy(['owner' => $owner, 'group' => $group]);

        $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $group);

        if($result && (($registeredGroup && $registeredGroup->unique) || $app->getRegisteredImageTransformation($group) || (!$registeredGroup && !$app->getRegisteredImageTransformation($group))))
            $result = $result[0];


        return $result;
    }

    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $result = $repo->findOneBy(['owner' => $owner, 'group' => $group]);

        return $result;
    }

    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $files = $repo->findBy(['owner' => $owner]);

        $result = [];

        if($files){
            foreach($files as $file){
                $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $file->group);
                if($registeredGroup && $registeredGroup->unique){
                    $result[trim($file->group)] = $file;
                }else{
                    if(!key_exists($file->group, $result))
                        $result[trim($file->group)] = [];

                    $result[trim($file->group)][] = $file;
                }
            }
            ksort($result);
        }


        return $result;
    }
}