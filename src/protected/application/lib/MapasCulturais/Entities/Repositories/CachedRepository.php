<?php
namespace MapasCulturais\Entities\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\LockMode;

use MapasCulturais\App;

class CachedRepository extends EntityRepository{
    function find($id, $lockMode = LockMode::NONE, $lockVersion = null) {
        $app = App::i();

        $cache_id = $this->getClassName() . "::{$id}";

        if($lockMode === LockMode::NONE && $lockVersion === null && $app->objectCacheEnabled() && $app->cache && $app->cache->contains($cache_id)){
            $result = $app->cache->fetch($cache_id);
        }else{
            $result = parent::find($id, $lockMode, $lockVersion);

            if($lockMode === LockMode::NONE && $lockVersion === null && $app->cache)
                $app->cache->save($cache_id, $result, $app->objectCacheTimeout());

        }
        return $result;
    }
}