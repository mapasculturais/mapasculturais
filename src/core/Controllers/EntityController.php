<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;

/**
 * Classe base para controladores de entidades
 *
 * Esta classe abstrata estende a classe base Controller e fornece funcionalidades
 * específicas para manipulação de entidades do sistema Mapas Culturais.
 * Ela utiliza traits para adicionar ações, visualizações e operações de entidade.
 *
 * @property-read \MapasCulturais\Entity $newEntity Uma nova instância vazia da entidade relacionada a este controlador
 * @property-read \Doctrine\ORM\EntityRepository $repository O repositório Doctrine da entidade com o mesmo nome do controlador no mesmo namespace pai
 * @property-read array $fields Os campos da entidade com o mesmo nome do controlador no mesmo namespace pai
 * @property-read \MapasCulturais\Entity $requestedEntity A entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
abstract class EntityController extends \MapasCulturais\Controller{
    use Traits\ControllerEntity,
        Traits\ControllerEntityActions,
        Traits\ControllerEntityViews;

    /**
     * Construtor do controlador.
     *
     * Este método define o nome da classe da entidade do controlador com uma classe
     * que tem o mesmo nome do controlador no namespace pai (substituindo "Controllers" por "Entities").
     *
     * @see \MapasCulturais\Controller::$entityClassName
     */
    protected function __construct() {
        $this->entityClassName = preg_replace("#Controllers\\\([^\\\]+)$#", 'Entities\\\$1', get_class($this));
    }
}