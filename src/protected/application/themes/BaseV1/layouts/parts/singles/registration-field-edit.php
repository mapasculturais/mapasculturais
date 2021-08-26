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
    <a ng-if="field.file" class="attachment-title" href="{{field.file.url}}" target="_blank" rel='noopener noreferrer'>{{field.file.name}}</a>

    <div>
        <!-- se já subiu o arquivo-->
        <!-- se não subiu ainda -->
        <a class="btn btn-default" ng-class="{'send':!field.file,'edit':field.file}" ng-click="openFileEditBox(field.id, $index, $event)" title="{{!field.file ? 'enviar' : 'editar'}} <?php \MapasCulturais\i::_e("anexo"); ?>">{{!field.file ? 'Enviar' : 'Editar'}}</a>
        <a class="btn btn-default delete" ng-if="!field.required && field.file" ng-click="removeFile(field.id, $index)" title="<?php \MapasCulturais\i::esc_attr_e("excluir anexo"); ?>"><?php \MapasCulturais\i::_e("Excluir"); ?></a>
    </div>
    <div ng-repeat="error in field.error" class="alert danger">{{error}}</div>
    <edit-box id="editbox-file-{{::field.id}}" position="bottom" title="{{::field.title}} {{::field.required ? '*' : ''}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar"); ?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Enviar anexo"); ?>" loading-label="<?php \MapasCulturais\i::esc_attr_e("Carregando ..."); ?>" on-submit="sendFile" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">

        <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{::field.groupName}}" enctype="multipart/form-data">
            <div class="alert danger hidden"></div>
            <p class="form-help"><?php \MapasCulturais\i::_e("Tamanho máximo do arquivo:"); ?> {{maxUploadSizeFormatted}}</p>
            <input type="file" name="{{::field.groupName}}" />

            <div class="js-ajax-upload-progress">
                <div class="progress">
                    <div class="bar"></div>
                    <div class="percent">0%</div>
                </div>
            </div>
        </form>
    </edit-box>
</div>