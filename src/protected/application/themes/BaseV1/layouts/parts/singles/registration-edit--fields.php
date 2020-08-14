<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset registration-edit-mode">
    <!--
    <h4><?php \MapasCulturais\i::_e("Campos adicionais");?></h4>
    <p class="registration-help"><?php \MapasCulturais\i::_e("Para efetuar sua inscrição, informe os campos abaixo.");?></p>
    -->
    <ul class="attachment-list" ng-controller="RegistrationFieldsController">
        <li ng-repeat="field in data.fields" ng-if="showFieldForCategory(field)" on-repeat-done="registration-fields" class="attachment-list-item registration-edit-mode attachment-list-item-type-{{field.fieldType}}">
            <?php 
            $definitions = \MapasCulturais\App::i()->getRegisteredRegistrationFieldTypes();

            foreach($definitions as $def) {
                $this->part($def->viewTemplate);
            }
            ?>

            <div ng-if="field.fieldType === 'file'" id="registration-file-{{field.id}}" >
                <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
                <div class="attachment-description">
                    <span ng-if="field.description">{{field.description}}</span>
                    <span ng-if="field.template">
                        (<a class="attachment-template" target="_blank" href="{{field.template.url}}" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("baixar modelo");?></a>)
                    </span>
                </div>
                <a ng-if="field.file" class="attachment-title" href="{{field.file.url}}" target="_blank" rel='noopener noreferrer'>{{field.file.name}}</a>

                <div class="btn-group">
                    <!-- se já subiu o arquivo-->
                    <!-- se não subiu ainda -->
                    <a class="btn btn-default hltip" ng-class="{'send':!field.file,'edit':field.file}" ng-click="openFileEditBox(field.id, $index, $event)" title="{{!field.file ? 'enviar' : 'editar'}} <?php \MapasCulturais\i::_e("anexo");?>">{{!field.file ? 'Enviar' : 'Editar'}}</a>
                    <a class="btn btn-default delete hltip" ng-if="!field.required && field.file" ng-click="removeFile(field.id, $index)" title="<?php \MapasCulturais\i::esc_attr_e("excluir anexo");?>"><?php \MapasCulturais\i::_e("Excluir");?></a>
                </div>

                <edit-box id="editbox-file-{{field.id}}" position="bottom" title="{{field.title}} {{field.required ? '*' : ''}}"
                          cancel-label ="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>"
                          submit-label ="<?php \MapasCulturais\i::esc_attr_e("Enviar anexo");?>"
                          loading-label="<?php \MapasCulturais\i::esc_attr_e("Carregando ...");?>"
                          on-submit="sendFile" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">

                    <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{field.groupName}}"  enctype="multipart/form-data">
                        <div class="alert danger hidden"></div>
                        <p class="form-help"><?php \MapasCulturais\i::_e("Tamanho máximo do arquivo:");?> {{maxUploadSizeFormatted}}</p>
                        <input type="file" name="{{field.groupName}}" />

                        <div class="js-ajax-upload-progress">
                            <div class="progress">
                                <div class="bar"></div>
                                <div class="percent">0%</div>
                            </div>
                        </div>
                    </form>
                </edit-box>
            </div>
        </li>
    </ul>
</div>
