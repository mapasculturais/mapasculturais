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
            "type" => $field->fieldType ?? 'attachment',
            "categories" => $field->categories,
            "proponentTypes" => $field->proponentTypes,
            "registrationRanges" => $field->registrationRanges,
        ];
    }
}
$this->jsObject['config']['opportunitySupportConfig'] = $result;
