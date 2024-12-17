<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <div class="registration-step" ng-controller="RegistrationFieldsController" ng-repeat="(stepName, fields) in data.fieldsByStep">
        <div class="registration-step__title" ng-if="fields.filter(showField).length > 0 && stepName">{{ $index + 1 }}. {{stepName}} </div>

        <?php $this->applyTemplateHook('registration-field-list', 'before') ?>
        <ul class="attachment-list">
            <?php $this->applyTemplateHook('registration-field-list', 'begin') ?>

            <li ng-repeat="field in fields" data-field-type="{{getFieldType(field)}}" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : 'section-title'">
                <?php $this->part('singles/registration-field-view') ?>
            </li>

            <?php $this->applyTemplateHook('registration-field-list', 'end') ?>
        </ul>
        <?php $this->applyTemplateHook('registration-field-list', 'after') ?>

    </div>    
</div>