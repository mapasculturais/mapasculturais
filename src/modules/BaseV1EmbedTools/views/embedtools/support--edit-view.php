<?php
$this->jsObject['isEditable'] = true;
$this->jsObject['userAllowedFields'] = $userAllowedFields;
$this->jsObject['entity']['hasControl'] = $entity->isUserAdmin($app->user) ? $entity->canUser('@control') : false;
$this->jsObject['isUserSupport'] = $entity->opportunity->isSupportUser($app->user);

$this->part('support-edit-view', [
    'entity' => $entity,
    'userAllowedFields' => $userAllowedFields
]);
