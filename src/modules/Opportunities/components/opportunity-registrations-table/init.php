<?php

$queryRegistration = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Registration::class, [
    '@select' => 'id,number,category,status,createTimestamp,owner.{id,name,files.avatar,}',
    'status' => 'GTE(0)',
    'opportunity' => "EQ({$this->controller->requestedEntity->id})",
    // '@permissions' => 'privateData',
]);

$registrations = $queryRegistration->getFindResult();

$this->jsObject['config']['opportunityRegistrationsTable']['registrations'] = $registrations;

