<?php
$entity = $this->controller->requestedEntity;

$config = [
    'registrationId' => $entity->id
];

$this->jsObject['config']['opportunityClaimForm'] = $config;