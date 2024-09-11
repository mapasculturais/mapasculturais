<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 $entity = $this->controller->requestedEntity;

 $class = $entity->getClassName();


 $dict_entity = function($class) {
    switch ($class) {
        case 'MapasCulturais\Entities\Agent':
            $result = "agent";
            break;
        case 'MapasCulturais\Entities\Opprotunity':
            $result = "opportunity";
            break;
        case 'MapasCulturais\Entities\Project':
            $result = "project";
            break;
        case 'MapasCulturais\Entities\Event':
            $result = "event";
            break;
        case 'MapasCulturais\Entities\Space':
            $result = "space";
            break;
    
        default:
            $result = null;
            break;
    }
    return $result;
 };

 $has_request =  false;
 $request_data =  [];
 if($requests = $app->repo("Request")->findBy(["originType" => $class, "originId" => $entity->id])) {
     $has_request = true;
     $count = count($requests);
    foreach($requests as $request) {
        
        $request_data = [
            'status' => $request->status,
            'originEntity' => $dict_entity($request->originType),
            'originType' => $request->originType,
            'originId' => $request->originId,
            'destinationEntity' => $dict_entity($request->destinationType),
            'destinationType' => $request->destinationType,
            'destinationId' => $request->destinationId,
            'count' => $count
        ];
    }
 }

 $this->jsObject['config']['entityOwner'] = [
    'hasRequest' => $has_request,
    'requestData' => $request_data
 ];