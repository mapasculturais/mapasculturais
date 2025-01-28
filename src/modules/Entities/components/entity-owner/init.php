<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;

$class = $entity->getClassName();

$has_request =  false;
$request_data =  [];
$destinationName = "";
if ($request = $app->repo("RequestChangeOwnership")->findOneBy(["originType" => $class, "originId" => $entity->id])) {
    $destination = $app->repo('Agent')->find($request->destinationId);
    $has_request = true;
    if($entity->ownerUser->id == $request->requesterUser->id) {
        // ceder propriedade
        $destinationName =  $destination->ownerUser->profile->name;
    } else {
        // reinvindicar propriedade
        $destinationName = $entity->ownerUser->profile->name;
    }

    $request_data = [
        'id' => $request->id,
        'status' => $request->status,
    ];
}

$this->jsObject['config']['entityOwner'] = [
    'hasRequest' => $has_request,
    'requestData' => $request_data,
    'destinationName' => $destinationName
];
