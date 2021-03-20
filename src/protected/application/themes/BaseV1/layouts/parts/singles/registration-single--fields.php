<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <!--
    <h4><?php \MapasCulturais\i::_e("Campos adicionais do formulário de inscrição.");?></h4>
    -->
    
    <ul class="attachment-list" ng-controller="RegistrationFieldsController">

        <li ng-repeat="field in data.fields" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : ''">
            <?php $this->part('singles/registration-field-view') ?>
        </li>
    </ul>
</div>
