<?php
namespace MapasCulturais\Traits;


trait ControllerAPINested{
    function API_getChildrenIds(){
        $entity = $this->requestedEntity;

        $ids = $entity->getChildrenIds();

        $this->apiResponse($ids);
    }
}