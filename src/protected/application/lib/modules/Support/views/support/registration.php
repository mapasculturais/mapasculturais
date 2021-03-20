<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);
$this->includeEditableEntityAssets();

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="OpportunityController">
    <?php $this->part('singles/registration--header', $_params); ?>

    <article>
        <?php $this->applyTemplateHook('form','begin'); ?>
        <div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
            <!--
            <h4><?php \MapasCulturais\i::_e("Campos adicionais do formulário de inscrição.");?></h4>
            -->
            <?php $this->applyTemplateHook('registration-field-list', 'before') ?>
            <ul class="attachment-list" ng-controller="RegistrationFieldsController">
                <?php $this->applyTemplateHook('registration-field-list', 'begin') ?>
                <li ng-repeat="field in data.fields" ng-if="showField(field) && field.canUserView" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : ''">
                    <div ng-if="field.canUserEdit">
                        <?php $this->part('singles/registration-field-edit') ?>
                    </div>
                    <div ng-if="!field.canUserEdit">
                        <?php $this->part('singles/registration-field-view') ?>
                    </div>
                </li>
                <?php $this->applyTemplateHook('registration-field-list', 'end') ?>
            </ul>
            <?php $this->applyTemplateHook('registration-field-list', 'after') ?>
        </div>

        <?php $this->applyTemplateHook('form','end'); ?>
    </article>
</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
