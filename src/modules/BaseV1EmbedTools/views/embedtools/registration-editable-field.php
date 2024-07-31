<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$avaliable_evaluationFields = $entity->opportunity->avaliableEvaluationFields ?? [];
$avaliable_evaluationFields['proponentType'] = true;
$avaliable_evaluationFields['range'] = true;
$avaliable_evaluationFields['category'] = true;

$app->view->jsObject['avaliableEvaluationFields'] = $avaliable_evaluationFields;

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];

$this->jsObject['registrationEditableFields'] = [
    'fields' => $entity->editableFields,
    'until' => $entity->editableUntil,
    'sentTimestamp' => $entity->editSentTimestamp,
    'canUserSendEditableFields' => $entity->canUser('sendEditableFields') 
];
?>

<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <!--
    <h4><?php \MapasCulturais\i::_e("Campos adicionais do formulário de inscrição."); ?></h4>
    -->
    <?php $this->applyTemplateHook('registration-field-list', 'before') ?>
    <ul class="attachment-list" ng-controller="RegistrationFieldsController">
        <?php $this->applyTemplateHook('registration-field-list', 'begin') ?>
        <li ng-repeat="field in data.fields" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : 'section-title'">
            <span ng-if="canUserEdit(field)">
                <?php $this->part('singles/registration-field-edit') ?>
            </span>
            <span ng-if="!canUserEdit(field)">
                <?php $this->part('singles/registration-field-view') ?>
            </span>
        </li>
        <?php $this->applyTemplateHook('registration-field-list', 'end') ?>
    </ul>
    <?php $this->applyTemplateHook('registration-field-list', 'after') ?>
</div>