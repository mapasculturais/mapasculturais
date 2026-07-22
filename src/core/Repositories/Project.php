<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

/**
 * Repositório para entidades de projeto
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de entidades do tipo Project no sistema.
 * 
 * @package MapasCulturais\Repositories
 */
class Project extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;
}
