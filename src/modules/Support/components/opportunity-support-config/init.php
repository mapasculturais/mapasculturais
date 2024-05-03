<?php
$entity = $this->controller->requestedEntity;

$result = [];
$fields = $entity->registrationFieldConfigurations;
$files = $entity->registrationFileConfigurations;

$fields_list = array_merge($fields, $files);

foreach ($fields_list as $field) {
    $result[] = [
        "id" => $field->id,
        "title" => $field->title,
        "ref" => $field->fieldType == "text" ? $field->fieldName : $field->fileGroupName,
        "typeDescription" => $field->fieldType == "text" ? $field->fieldType : "Arquivo",
    ];
}
$this->jsObject['config']['opportunitySupportConfig'] = $result;
