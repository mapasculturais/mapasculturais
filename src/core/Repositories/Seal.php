<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

/**
 * Repositório para entidades de selo
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de entidades do tipo Seal no sistema.
 * 
 * @package MapasCulturais\Repositories
 */
class Seal extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;
}
