<?php

namespace Tests\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\EntityRevisionData;

trait RevisionCleanup
{
    /**
     * Remove do UnitOfWork as revisões que já foram gravadas no banco.
     *
     * Cada save de entidade cria revisões que nunca são lidas de novo pela memória
     * (getLastRevision busca no banco), mas o Doctrine as revisa em todo flush.
     * Em cenários com muitas inscrições isso deixa cada flush mais lento que o anterior.
     */
    protected function detachFlushedRevisions(): void
    {
        $em = App::i()->em;
        $uow = $em->getUnitOfWork();
        $identity_map = $uow->getIdentityMap();

        foreach ([EntityRevision::class, EntityRevisionData::class] as $class) {
            foreach ($identity_map[$class] ?? [] as $entity) {
                if ($entity->id && !$uow->isEntityScheduled($entity)) {
                    $em->detach($entity);
                }
            }
        }
    }
}
