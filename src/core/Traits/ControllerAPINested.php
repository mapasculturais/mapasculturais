<?php
namespace MapasCulturais\Traits;


/**
 * Trait para controladores que trabalham com entidades aninhadas (hierárquicas)
 * 
 * Este trait fornece métodos para manipulação de entidades que possuem relações
 * pai-filho, permitindo operações específicas para hierarquias de entidades.
 * 
 * @package MapasCulturais\Traits
 */
trait ControllerAPINested{
    
    /**
     * Retorna os IDs das entidades filhas da entidade solicitada
     * 
     * Este método é acessível via API e retorna um array com os IDs
     * de todas as entidades filhas da entidade atual.
     * 
     * @api GET getChildrenIds
     * @return void A resposta é enviada via $this->apiResponse()
     * 
     * @uses \MapasCulturais\Entity::getChildrenIds()
     */
    function API_getChildrenIds(){
        $entity = $this->requestedEntity;

        $ids = $entity->getChildrenIds();

        $this->apiResponse($ids);
    }
}