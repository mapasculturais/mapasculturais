<?php
$entity = $this->controller->requestedEntity;

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
            "displayOrder" => $field->displayOrder,
        ];
    }
}

usort($result, fn($field1, $field2) => $field1['displayOrder'] <=> $field2['displayOrder']);

$this->jsObject['config']['registrationEditableFields'] = $result;