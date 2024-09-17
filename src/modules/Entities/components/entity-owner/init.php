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
if ($request = $app->repo("Request")->findBy(["originType" => $class, "originId" => $entity->id])) {
    $destination = $app->repo('Agent')->find($request[0]->destinationId);
    $has_request = true;
    $destinationName = $destination->name;

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
