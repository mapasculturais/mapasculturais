<div id="registration-attachments" class="registration-fieldset">

    <h4>6. <?php \MapasCulturais\i::_e("Campos");?></h4>
    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e("Configure aqui os campos do formulário de inscrição.");?></p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e("A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto.");?> </p>
    <div ng-controller="RegistrationConfigurationsController">
        <?php if ($this->controller->action == 'create'): ?>
            <p class="allert warning"><?php \MapasCulturais\i::_e("Antes de configurar os campos é preciso salvar o projeto.");?></p>
        <?php else: ?>
            <p ng-if="data.entity.canUserModifyRegistrationFields" >
                <a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-fields', $event)"><?php \MapasCulturais\i::_e("Adicionar campo");?></a>
                <a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-files', $event)"><?php \MapasCulturais\i::_e("Adicionar anexo");?></a>
            </p>
        <?php endif; ?>
        <!-- edit-box to add attachment -->

        <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-fields" position="right" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar campo");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Criar");?>" close-on-cancel='true' on-cancel="closeNewFieldConfigurationEditBox" on-submit="createFieldConfiguration" spinner-condition="data.fieldSpinner">
            <select ng-model="data.newFieldConfiguration.fieldType" ng-options="value.slug as value.name disable when value.disabled for value in data.fieldTypes" ></select>
            <input type="text" ng-model="data.newFieldConfiguration.title" placeholder="<?php \MapasCulturais\i::esc_attr_e("Nome do campo");?>"/>
            <textarea ng-model="data.newFieldConfiguration.description" placeholder="<?php \MapasCulturais\i::esc_attr_e("Descrição do campo");?>"/></textarea>
            <div ng-show="data.fieldsWithOptions.indexOf(data.newFieldConfiguration.fieldType) >= 0">
                <textarea ng-model="data.newFieldConfiguration.fieldOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
                <p class="registration-help"><?php \MapasCulturais\i::_e("Informe uma opção por linha.");?></p>
            </div>
            <div ng-if="data.newFieldConfiguration.fieldType == 'textarea' || data.newFieldConfiguration.fieldType == 'text' ">
                <input type="text" ng-model="data.newFieldConfiguration.maxSize" placeholder="<?php \MapasCulturais\i::esc_attr_e("Quantidade máxima de caracteres");?>"/>
            </div>
            <p><label><input type="checkbox" ng-model="data.newFieldConfiguration.required"> <small><?php \MapasCulturais\i::_e("O preenchimento deste campo é obrigatório");?></small></label></p>
            <p ng-if="data.categories.length > 1">
                <small><?php \MapasCulturais\i::_e("Selecione em quais categorias este campo é utilizado");?>:</small><br>
                <label><input type="checkbox" onclick="if (!this.checked)
                    return false" ng-click="data.newFieldConfiguration.categories = []" ng-checked="allCategories(data.newFieldConfiguration)"> <?php \MapasCulturais\i::_e("Todas");?> </label>
                    <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="data.newFieldConfiguration.categories" checklist-value="category"> {{category}} </label>
                </p>
            </edit-box>

            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files" position="right" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar anexo");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Criar");?>" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
                <input type="text" ng-model="data.newFileConfiguration.title" placeholder="<?php \MapasCulturais\i::esc_attr_e("Nome do anexo");?>"/>
                <textarea ng-model="data.newFileConfiguration.description" placeholder="<?php \MapasCulturais\i::esc_attr_e("Descrição do anexo");?>"/></textarea>
                <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required"> <small><?php \MapasCulturais\i::_e("O envio deste anexo é obrigatório");?></small></label></p>
                <p ng-if="data.categories.length > 1">
                    <small><?php \MapasCulturais\i::_e("Selecione em quais categorias este anexo é utilizado");?>:</small><br>
                    <label><input type="checkbox" onclick="if (!this.checked)
                        return false" ng-click="data.newFileConfiguration.categories = []" ng-checked="allCategories(data.newFileConfiguration)"> <?php \MapasCulturais\i::_e("Todas");?> </label>
                        <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="data.newFileConfiguration.categories" checklist-value="category"> {{category}} </label>
                    </p>
                </edit-box>

                <!-- added attachments list -->
                <ul class="attachment-list">
                    <li ng-repeat="field in data.fields" on-repeat-done="init-ajax-uploaders" id="field-{{field.type}}-{{field.id}}" class="attachment-list-item">
                        <div ng-if="field.fieldType !== 'file'">
                            <div class="js-open-editbox">
                                <div class="label">{{field.title}} <em><small>({{field.required.toString() === 'true' ? data.fieldsRequiredLabel : data.fieldsOptionalLabel }})</small></em></div>
                                <span ng-if="field.categories.length" class="attachment-description"> 
                                    <?php \MapasCulturais\i::_e("Somente para");?> <strong>{{field.categories.join(', ')}}</strong>
                                    <br>
                                </span>
                                <span class="attachment-description">
                                    <?php \MapasCulturais\i::_e("Tipo");?>: <strong>{{data.fieldTypesBySlug[field.fieldType].name}}</strong>
                                    <br>
                                </span>
                                <span ng-if="field.description" class="attachment-description">
                                    <?php \MapasCulturais\i::_e("Descrição");?>: {{field.description}}
                                </span>
                            </div>
                            <!-- edit-box to edit attachment -->
                            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-field-{{field.id}}" position="left" title="<?php \MapasCulturais\i::esc_attr_e("Editar Campo");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Salvar");?>" close-on-cancel='true' on-cancel="cancelFieldConfigurationEditBox" on-submit="editFieldConfiguration" index="{{$index}}" spinner-condition="data.fieldSpinner">
                                <select ng-model="field.fieldType" ng-options="value.slug as value.name disable when value.disabled for value in data.fieldTypes" ></select>
                                <input type="text" ng-model="field.title" placeholder="<?php \MapasCulturais\i::esc_attr_e("Nome do campo");?>"/>
                                <textarea ng-model="field.description" placeholder="<?php \MapasCulturais\i::esc_attr_e("Descrição do campo");?>"/></textarea>
                                <div ng-show="data.fieldsWithOptions.indexOf(field.fieldType) >= 0">
                                    <textarea ng-model="field.fieldOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
                                    <p class="registration-help"><?php \MapasCulturais\i::_e("Informe uma opção por linha.");?></p>
                                </div>
                                <div ng-if="field.fieldType == 'textarea' || fild.fieldType == 'text' ">
                                    <input type="text" ng-model="field.maxSize" placeholder="<?php \MapasCulturais\i::esc_attr_e("Quantidade máxima de caracteres");?>"/>
                                </div>
                                <p><label><input type="checkbox" ng-model="field.required"> <?php \MapasCulturais\i::_e("O preenchimento deste campo é obrigatório");?></label></p>
                                <p ng-if="data.categories.length > 1">
                                    <small><?php \MapasCulturais\i::_e("Selecione em quais categorias este campo é utilizado");?>:</small><br>
                                    <label><input type="checkbox" onclick="if (!this.checked)
                                        return false" ng-click="field.categories = []" ng-checked="allCategories(field)"> <?php \MapasCulturais\i::_e("Todas");?> </label>
                                        <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="field.categories" checklist-value="category"> {{category}} </label>
                                    </p>
                                </edit-box>

                                <div ng-if="data.entity.canUserModifyRegistrationFields" class="btn-group">
                                    <a ng-click="openFieldConfigurationEditBox(field.id, $index, $event);" class="btn btn-default edit hltip" title="<?php \MapasCulturais\i::esc_attr_e("editar campo");?>"></a>
                                    <a ng-click="removeFieldConfiguration(field.id, $index)" data-href="{{field.deleteUrl}}" class="btn btn-default delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("excluir campo");?>"></a>
                                </div>
                            </div>

                            <div ng-if="field.fieldType === 'file'">
                                <div class="js-open-editbox">
                                    <div class="label">{{field.title}} <em><small>({{field.required.toString() === 'true' ? 'Obrigatório' : 'Opcional'}})</small></em></div>
                                    <span ng-if="field.categories.length" class="attachment-description">
                                        <?php \MapasCulturais\i::_e("Somente para");?> <strong>{{field.categories.join(', ')}}</strong>
                                        <br>
                                    </span>
                                    <span class="attachment-description">
                                        <?php \MapasCulturais\i::_e("Tipo");?>: <strong><?php \MapasCulturais\i::_e("Anexo");?></strong>
                                        <br>
                                    </span>
                                    <span class="attachment-description">
                                        <?php \MapasCulturais\i::_e("Descrição");?>: {{field.description}}
                                    </span>
                                </div>
                                <!-- edit-box to edit attachment -->
                                <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-{{field.id}}" position="left" title="<?php \MapasCulturais\i::esc_attr_e("Editar Anexo");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Salvar");?>" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                                    <input type="text" ng-model="field.title" placeholder="<?php \MapasCulturais\i::esc_attr_e("Nome do anexo");?>"/>
                                    <textarea ng-model="field.description" placeholder="<?php \MapasCulturais\i::esc_attr_e("Descrição do anexo");?>"/></textarea>
                                    <p><label><input type="checkbox" ng-model="field.required" ng-checked="field.required"> <?php \MapasCulturais\i::_e("O envio deste anexo é obrigatório");?></label></p>

                                    <p ng-if="data.categories.length > 1">
                                        <small><?php \MapasCulturais\i::_e("Selecione em quais categorias este anexo é utilizado");?>:</small><br>
                                        <label><input type="checkbox" onclick="if (!this.checked)
                                            return false" ng-click="field.categories = []" ng-checked="allCategories(field)"> <?php \MapasCulturais\i::_e("Todas");?> </label>
                                            <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="field.categories" checklist-value="category"> {{category}} </label>
                                        </p>
                                    </edit-box>
                                    <div class="file-{{field.template.id}}" ng-if="field.template">
                                        <span ng-if="data.entity.canUserModifyRegistrationFields" class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(field.id, $index, $event);">{{field.template.name}}</span>
                                        <a ng-if="data.entity.canUserModifyRegistrationFields" class="delete hltip" ng-click="removeFileConfigurationTemplate(field.id, $index)" title="<?php \MapasCulturais\i::esc_attr_e("Excluir modelo");?>"></a>
                                    </div>
                                    <p ng-if="!data.entity.canUserModifyRegistrationFields">
                                        <a class="file-{{field.template.id}} attachment-template"  href="{{field.template.url}}" target="_blank">{{field.template.name}}</a>
                                    </p>
                                    <!-- edit-box to upload attachments -->
                                    <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-template-{{field.id}}" position="top" title="<?php \MapasCulturais\i::esc_attr_e("Enviar modelo");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" submit-label="<?php \MapasCulturais\i::esc_attr_e("Enviar modelo");?>" on-submit="sendFile" close-on-cancel='true' spinner-condition="data.uploadSpinner">
                                        <p ng-if="field.template">
                                            <a class="file-{{field.template.id}} attachment-template"  href="{{field.template.url}}" target="_blank">{{field.template.name}}</a>
                                        </p>
                                        <form class="js-ajax-upload" method="post" data-group="{{uploadFileGroup}}" action="{{getUploadUrl(field.id)}}" enctype="multipart/form-data">
                                            <div class="alert danger hidden"></div>
                                            <p class="form-help"><?php \MapasCulturais\i::_e("Tamanho máximo do arquivo");?>: {{maxUploadSizeFormatted}}</p>
                                            <input type="file" name="{{uploadFileGroup}}" />

                                            <div class="js-ajax-upload-progress">
                                                <div class="progress">
                                                    <div class="bar"></div >
                                                        <div class="percent">0%</div >
                                                        </div>
                                                    </div>

                                                </form>
                                            </edit-box>
                                            <div ng-if="data.entity.canUserModifyRegistrationFields" class="btn-group">
                                                <a ng-click="openFileConfigurationEditBox(field.id, $index, $event);" class="btn btn-default edit hltip" title="<?php \MapasCulturais\i::esc_attr_e("editar anexo");?>"></a>
                                                <a ng-if="!field.template" ng-click="openFileConfigurationTemplateEditBox(field.id, $index, $event);" class="btn btn-default send hltip" title="<?php \MapasCulturais\i::esc_attr_e("enviar modelo");?>" ></a>
                                                <a ng-click="removeFileConfiguration(field.id, $index)" data-href="{{field.deleteUrl}}" class="btn btn-default delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("excluir anexo");?>"></a>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
<!-- #registration-attachments -->
