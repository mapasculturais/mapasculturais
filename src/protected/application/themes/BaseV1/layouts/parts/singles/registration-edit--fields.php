<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset registration-edit-mode">
    <!--
    <h4><?php \MapasCulturais\i::_e("Campos adicionais");?></h4>
    <p class="registration-help"><?php \MapasCulturais\i::_e("Para efetuar sua inscrição, informe os campos abaixo.");?></p>
    -->
    <ul class="attachment-list">
        <li id="wrapper-{{field.fieldName}}" ng-repeat="field in ::data.fields" ng-if="showField(field)" on-repeat-done="registration-fields" data-field-name="{{field.fieldName}}" class="attachment-list-item registration-edit-mode attachment-list-item-type-{{field.fieldType}}">
            <span ng-if="lockedField(field)">
                <?php $this->part('singles/registration-field-view') ?>
            </span>
            <span ng-if="!lockedField(field)">    
                <?php $this->part('singles/registration-field-edit') ?>
            </span>
        </li>
    </ul>
</div>
