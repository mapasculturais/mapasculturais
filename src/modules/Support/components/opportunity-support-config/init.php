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
            "config" => $field->config,
            "order" => $field->displayOrder,
            "conditional" => $field->conditional,
            "conditionalField" => $field->conditionalField,
            "required" => $field->required,
            "displayOrder" => $field->displayOrder,
            "step" => $field->step ?? null,
        ];
    }
}

usort($result, function ($a, $b) {
    return $a['order'] <=> $b['order'];
});

$this->jsObject['config']['opportunitySupportConfig'] = $result;
