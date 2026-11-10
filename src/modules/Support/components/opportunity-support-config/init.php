<?php

use MapasCulturais\App;
use MapasCulturais\i;

$app = App::i();
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

// Pergunta de cotas do sistema (fica fora do formulário, no topo da inscrição)
if ($entity->enableQuotasQuestion) {
    array_unshift($result, [
        "id" => "appliedForQuota",
        "title" => i::__('Vai concorrer às cotas?'),
        "ref" => "appliedForQuota",
        "type" => "select",
        "categories" => [],
        "proponentTypes" => [],
        "registrationRanges" => [],
        "config" => null,
        "order" => -1,
        "conditional" => false,
        "conditionalField" => null,
        "required" => true,
        "displayOrder" => -1,
        "step" => ["displayOrder" => -1],
    ]);
}

$app->applyHook('component(opportunity-support-config).fields', [&$result, $entity]);

$this->jsObject['config']['opportunitySupportConfig'] = $result;
