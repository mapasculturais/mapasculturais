<?php
$entity = $this->controller->requestedEntity->opportunity;

$result = [];
$fields = $entity->registrationFieldConfigurations;
$files = $entity->registrationFileConfigurations;

$fields_list = array_merge($fields, $files);


foreach ($fields_list as $field) {
    if ($field->fieldType != "section") {
        $result[] = [
            "id" => $field->id,
            "title" => $field->title,
            "ref" => $field->fileGroupName ?? $field->fieldName,
        ];
    }
}
$this->jsObject['config']['registrationEditableFields'] = $result;