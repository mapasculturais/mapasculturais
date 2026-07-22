<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;

/**
 * Controlador para etapas de inscrição
 * 
 * Este controlador gerencia as etapas do processo de inscrição
 * em oportunidades no sistema Mapas Culturais.
 * 
 * @property-read \MapasCulturais\Entities\RegistrationStep $newEntity Nova instância vazia da entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório Doctrine da entidade
 * @property-read array $fields Campos da entidade
 * @property-read \MapasCulturais\Entities\RegistrationStep $requestedEntity Entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
class RegistrationStep extends \MapasCulturais\Controller {
    use Traits\ControllerEntity,
        Traits\ControllerEntityActions,
        Traits\ControllerAPI;

    /**
     * Construtor do controlador
     * 
     * Define o nome da classe da entidade como RegistrationStep
     * 
     * @see \MapasCulturais\Controller::$entityClassName
     */
    protected function __construct() {
        $this->entityClassName =  'MapasCulturais\Entities\RegistrationStep';
    }
}
