<?php

declare(strict_types=1);

namespace App\Repository;

use App\Connection\EntityManager;
use Doctrine\ORM\EntityManager as MapaCulturalEntityManager;
use MapasCulturais\App;

abstract class AbstractRepository
{
    protected MapaCulturalEntityManager $mapaCulturalEntityManager;
    protected EntityManager $entityManager;

    public function __construct()
    {
        $app = App::i();
        $this->mapaCulturalEntityManager = $app->em;
        $this->entityManager = new EntityManager();
    }
}
