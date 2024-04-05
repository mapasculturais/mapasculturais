<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use MapasCulturais\App;

abstract class AbstractRepository
{
    private EntityManager $entityManager;

    public function __construct()
    {
        $app = App::i();
        $this->entityManager = $app->em;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
