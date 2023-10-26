<article ng-controller="SupportForm">
    <?php $this->applyTemplateHook('form', 'begin'); ?>
    <h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição"); ?></h3>
    <div class="registration-fieldset registration-number">
        <h4><?php \MapasCulturais\i::_e("Número da Inscrição"); ?></h4>
        <div class="registration-id">
            <?php echo $entity->number ?>
        </div>
    </div>
    <div class="registration-fieldset ng-scope" ng-if="data.fields.length == 0"><?php \MapasCulturais\i::_e("Não existem campos disponíveis para suporte."); ?></div>
    <div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
        <!--
            <h4><?php \MapasCulturais\i::_e("Campos adicionais do formulário de inscrição."); ?></h4>
            -->
        <?php $this->applyTemplateHook('registration-field-list', 'before') ?>

        <ul class="attachment-list" ng-controller="RegistrationFieldsController">
            <?php $this->applyTemplateHook('registration-field-list', 'begin') ?>
            <li ng-repeat="field in data.fields" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class="{'js-field attachment-list-item registration-view-mode': (field.fieldType != 'section'), 'section': (field.fieldType == 'section')}">
                <div ng-if="canUserEdit(field)">
                    <?php $this->part('singles/registration-field-edit') ?>
                </div>
                <div ng-if="!canUserEdit(field)">
                    <?php $this->part('singles/registration-field-view') ?>
                </div>
            </li>
            <?php $this->applyTemplateHook('registration-field-list', 'end') ?>
        </ul>
        <?php $this->applyTemplateHook('registration-field-list', 'after') ?>
    </div>

    <?php $this->applyTemplateHook('form', 'end'); ?>
</article>