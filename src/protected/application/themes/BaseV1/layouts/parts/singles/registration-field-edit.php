{{ (fieldName = field.fieldName) && false ? '' : ''}}

<?php
$definitions = \MapasCulturais\App::i()->getRegisteredRegistrationFieldTypes();

foreach ($definitions as $def) {
    $this->part($def->viewTemplate);
}
?>

<div ng-if="::field.fieldType !== 'file'" ng-repeat="error in field.error" class="alert danger">{{error}}</div>

<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType === 'file'" id="file_{{::field.id}}">
    <span class="label">
        {{::field.title}}
        <div ng-if="::field.required " class="field-required"><span class="description"><?php \MapasCulturais\i::_e('obrigatório'); ?></span><span class="icon-required">*</span></div>
    </span>

    <div class="attachment-description">
        <span ng-if="::field.description">{{::field.description}}</span>
        <span ng-if="::field.template">
            (<a class="attachment-template" target="_blank" href="{{::field.template.url}}" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("baixar modelo"); ?></a>)
        </span>
    </div>
    <ul ng-if="!field.multiple && field.file" class="widget-list js-downloads js-slimScroll">
        <li class="widget-list-item is-editable">
            <a href="{{field.file.url}}" target="_blank" rel='noopener noreferrer'><span>{{field.file.name}}</span></a>
            <div class="botoes">
                <a hltip ng-click="removeFile(field.file)" class="delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("Excluir arquivo");?>"></a>
            </div>
        </li>
    </ul>

    <ul ng-if="field.multiple" class="widget-list js-downloads js-slimScroll">
        <li ng-repeat="file in field.file track by $index" id="{{file.id}}" class="widget-list-item is-editable">
            <a href="{{file.url}}" target="_blank" rel='noopener noreferrer'><span>{{file.description}} - {{file.name}}</span></a>
            <div class="botoes">
                <a hltip ng-click="removeFile(file)" class="delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("Excluir arquivo");?>"></a>
            </div>
        </li>
    </ul>

    <div class="btn-group" ng-if="::!field.multiple">
        <!-- se já subiu o arquivo-->
        <!-- se não subiu ainda -->
        <a class="btn btn-default" ng-class="{'send':!field.file,'edit':field.file}" ng-click="openFileEditBox(field.id, $index, $event)" title="{{!field.file ? '<?php \MapasCulturais\i::_e("enviar") ?>' : '<?php \MapasCulturais\i::_e("editar") ?>'}} <?php \MapasCulturais\i::_e("anexo");?>">{{!field.file ? '<?php \MapasCulturais\i::_e("Enviar") ?>' : '<?php \MapasCulturais\i::_e("Editar") ?>'}}</a>
    </div>
    <div class="btn-group" ng-if="::field.multiple">
        <!-- se já subiu o arquivo-->
        <!-- se não subiu ainda -->
        <a class="btn btn-default send" ng-click="openFileEditBox(field.id, $index, $event)" title="<?php \MapasCulturais\i::esc_attr_e("Enviar Anexo") ?>"><?php \MapasCulturais\i::_e("Enviar") ?></a>
    </div>
    <div ng-repeat="error in field.error" class="alert danger">{{error}}</div>
    <edit-box id="editbox-file-{{::field.id}}" position="bottom" title="{{::field.title}} {{::field.required ? '*' : ''}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar"); ?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Enviar anexo"); ?>" loading-label="<?php \MapasCulturais\i::esc_attr_e("Carregando ..."); ?>" on-submit="sendFile" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">
        <?php $this->part('singles/registration-edit--upload-form') ?>
    </edit-box>
</div>