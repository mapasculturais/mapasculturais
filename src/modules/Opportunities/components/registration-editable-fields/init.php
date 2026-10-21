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
            "displayOrder" => $field->displayOrder,
        ];
    }
}

usort($result, fn($field1, $field2) => $field1['displayOrder'] <=> $field2['displayOrder']);

// Pergunta de cotas do sistema (fica fora do formulário, no topo da inscrição)
if ($entity->enableQuotasQuestion) {
    array_unshift($result, [
        "id" => "appliedForQuota",
        "title" => i::__('Vai concorrer às cotas?'),
        "ref" => "appliedForQuota",
        "displayOrder" => -1,
    ]);
}

$app->applyHook('component(registration-editable-fields).fields', [&$result, $entity]);

$this->jsObject['config']['registrationEditableFields'] = $result;