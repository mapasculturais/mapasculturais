<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;

/**
 * Controlador para avaliações de inscrições
 * 
 * Este controlador gerencia as operações relacionadas a avaliações
 * de inscrições no sistema Mapas Culturais.
 * 
 * @property-read \MapasCulturais\Entities\RegistrationEvaluation $newEntity Nova instância vazia da entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório Doctrine da entidade
 * @property-read array $fields Campos da entidade
 * @property-read \MapasCulturais\Entities\RegistrationEvaluation $requestedEntity Entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
class RegistrationEvaluation extends EntityController
{
    use Traits\ControllerAPI,
        Traits\ControllerUploads;
}