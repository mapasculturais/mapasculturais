<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Request;

$entity = $this->controller->requestedEntity;

if (!$entity || $entity->getClassName() !== 'MapasCulturais\Entities\Space') {
    return;
}

$class = $entity->getClassName();

$pending_sent = [];
$pending_received = [];

$sent = $app->repo('RequestChildEntity')->findBy([
    'originType' => $class,
    'originId' => $entity->id,
    'status' => Request::STATUS_PENDING,
]);

foreach ($sent as $request) {
    $destination = $request->getDestination();
    $notifications = $request->notifications;
    $notification = $notifications && count($notifications) ? $notifications[0] : null;
    $pending_sent[] = [
        'requestId' => $request->id,
        'notificationId' => $notification?->id,
        'spaceId' => $destination?->id,
        'space' => $destination ? $destination->simplify('id,name,type,terms,files.avatar,singleUrl,endereco,shortDescription') : null,
    ];
}

$received = $app->repo('RequestChildEntity')->findBy([
    'destinationType' => $class,
    'destinationId' => $entity->id,
    'status' => Request::STATUS_PENDING,
]);

foreach ($received as $request) {
    $origin = $request->getOrigin();
    $notifications = $request->notifications;
    $notification = $notifications && count($notifications) ? $notifications[0] : null;
    $pending_received[] = [
        'requestId' => $request->id,
        'notificationId' => $notification?->id,
        'spaceId' => $origin?->id,
        'space' => $origin ? $origin->simplify('id,name,type,terms,files.avatar,singleUrl,endereco,shortDescription') : null,
    ];
}

$this->jsObject['config']['entitySpacesEdit'] = [
    'pendingSent' => $pending_sent,
    'pendingReceived' => $pending_received,
];
