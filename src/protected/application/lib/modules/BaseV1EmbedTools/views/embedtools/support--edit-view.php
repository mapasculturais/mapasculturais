<?php
$fields = $entity->opportunity->registrationFieldConfigurations;
$this->jsObject['entity']['hasControl'] = $entity->isUserAdmin($app->user) ? $entity->canUser('@control') : false;
$this->jsObject['isEditable'] = true;

if (!$entity->canUser('@control')) {
    foreach ($fields as $key => $f) {
        $name = $f->fieldName;
        if (!isset($userAllowedFields[$name])) {
            unset($fields[$key]);
        }
    }
}
$this->jsObject['entity']['registrationFieldConfigurations'] = array_values($fields);
$this->jsObject['userAllowedFields'] = $userAllowedFields;

// Verify allowed files
$files = $entity->opportunity->registrationFileConfigurations;
foreach ($files as $key => $f) {
    $name = $f->getFileGroupName();
    if (!isset($userAllowedFields[$name])) {
        unset($files[$key]);
    }
}
$this->jsObject['entity']['registrationFileConfigurations'] = array_values($files);

$this->part('support-edit-view', [
    'entity' => $entity,
    'userAllowedFields' => $userAllowedFields
]);
