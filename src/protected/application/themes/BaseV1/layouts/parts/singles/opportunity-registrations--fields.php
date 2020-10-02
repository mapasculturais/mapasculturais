<?php
use MapasCulturais\i;

$can_edit = $entity->canUser('modifyRegistrationFields');

$editable_class = $can_edit ? 'js-editable' : '';

$definitions = \MapasCulturais\App::i()->getRegisteredRegistrationFieldTypes();

$app->view->jsObject['blockedOpportunityFields'] = [];
$app->applyHookBoundTo($this, 'opportunity.blockedFields', [$entity]);

?>

<div id="registration-attachments" class="registration-fieldset project-edit-mode">

    <h4><?php i::_e("Campos");?></h4>

    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help"><?php i::_e("Configure aqui os campos do formulário de inscrição.");?></p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help"><?php i::_e("A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto.");?> </p>
    <div ng-controller="RegistrationConfigurationsController">
        <?php if ($this->controller->action == 'create'): ?>
            <p class="allert warning"><?php i::_e("Antes de configurar os campos é preciso salvar o projeto.");?></p>
        <?php else: ?>
            <?php $this->part('singles/opportunity-registrations--fields--project-name', ['editable_class' => $editable_class, 'entity' => $entity]) ?>

            <p ng-if="data.entity.canUserModifyRegistrationFields" >
                <a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-fields', $event)" rel='noopener noreferrer'><?php i::_e("Adicionar campo");?></a>
                <a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-files', $event)" rel='noopener noreferrer'><?php i::_e("Adicionar anexo");?></a>
            </p>
            <?php endif; ?>
            <!-- edit-box to add attachment -->
            
            <edit-box  ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-fields" position="right" title="<?php i::esc_attr_e("Adicionar campo");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" submit-label="<?php i::esc_attr_e("Criar");?>" close-on-cancel='true' on-cancel="closeNewFieldConfigurationEditBox" on-submit="createFieldConfiguration" spinner-condition="data.fieldSpinner">
            <label>
                <?php i::_e('Nome do campo') ?><br>
                <input type="text" ng-model="data.newFieldConfiguration.title" placeholder="<?php i::esc_attr_e("Nome do campo");?>"/>
            </label>
            <label>
                <?php i::_e('Descrição do campo') ?><br>
                <textarea ng-model="data.newFieldConfiguration.description" placeholder="<?php i::esc_attr_e("Descrição do campo");?>"/></textarea>
            </label>
            <label>
                <?php i::_e('Tipo do campo') ?><br>
                <select ng-model="data.newFieldConfiguration.fieldType" ng-options="value.slug as value.name disable when value.disabled for value in data.fieldTypes" ></select>
            </label>
            {{ (field = data.newFieldConfiguration) && false ? '' : ''}}
            <?php 
            foreach($definitions as $def) {
                $this->part($def->configTemplate);
            }
            ?>
        
            <?php $this->part('singles/opportunity-registrations--fields--field-require'); ?>

            <p ng-if="data.categories.length > 1">
                <small><?php i::_e("Selecione em quais categorias este campo é utilizado");?>:</small><br>
                <label><input type="checkbox" onclick="if (!this.checked)
                    return false" ng-click="data.newFieldConfiguration.categories = []" ng-checked="allCategories(data.newFieldConfiguration)"> <?php i::_e("Todas");?> </label>
                    <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="data.newFieldConfiguration.categories" checklist-value="category"> {{category}} </label>
                </p>
            </edit-box>

            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files" position="right" title="<?php i::esc_attr_e("Adicionar anexo");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" submit-label="<?php i::esc_attr_e("Criar");?>" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
                <input type="text" ng-model="data.newFileConfiguration.title" placeholder="<?php i::esc_attr_e("Nome do anexo");?>"/>
                <textarea ng-model="data.newFileConfiguration.description" placeholder="<?php i::esc_attr_e("Descrição do anexo");?>"/></textarea>
                <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required"> <small><?php i::_e("O envio deste anexo é obrigatório");?></small></label></p>
                <p ng-if="data.categories.length > 1">
                    <small><?php i::_e("Selecione em quais categorias este anexo é utilizado");?>:</small><br>
                    <label><input type="checkbox" onclick="if (!this.checked)
                        return false" ng-click="data.newFileConfiguration.categories = []" ng-checked="allCategories(data.newFileConfiguration)"> <?php i::_e("Todas");?> </label>
                        <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="data.newFileConfiguration.categories" checklist-value="category"> {{category}} </label>
                    </p>
                </edit-box>

                <select ng-if="data.categories.length > 0" ng-model="data.filterFieldConfigurationByCategory">
                    <option value=''><?php i::_e('Exibir os campos de todas as categorias')?></option>
                    <option ng-repeat="category in data.categories" value="{{category}}"><?php i::_e('Exibir os campos da categoria "{{category}}"') ?></option>
                </select>
                <!-- added attachments list -->
                <ul ui-sortable="sortableOptions" class="attachment-list" ng-model="data.fields">
                    <li ng-repeat="field in data.fields" ng-show="showFieldConfiguration(field)" on-repeat-done="init-ajax-uploaders" id="field-{{field.type}}-{{field.id}}" class="attachment-list-item project-edit-mode attachment-list-item-type-{{field.fieldType}}">
                        <div ng-if="field.fieldType !== 'file'">
                            <div class="js-open-editbox">
                                <div class="label">
                                <code onclick="copyToClipboard(this)" class="hltip field-id" title="<?php i::esc_attr_e('Clique para copiar')?>">{{field.id}}</code>
                                    {{field.title}} <em  ng-if="field.fieldType !== 'section'"><small>({{field.required.toString() === 'true' ? data.fieldsRequiredLabel : data.fieldsOptionalLabel }})</small></em>
                                </div>
                                <span ng-if="field.categories.length" class="attachment-description">
                                    <?php i::_e("Somente para");?> <strong>{{field.categories.join(', ')}}</strong>
                                    <br>
                                </span>
                                <span class="attachment-description">
                                    <?php i::_e("Tipo");?>: <strong>{{data.fieldTypesBySlug[field.fieldType].name}}</strong>
                                    <br>
                                </span>
                                <span ng-if="field.description" class="attachment-description">
                                    <?php i::_e("Descrição");?>: {{field.description}}
                                </span>
                            </div>
                            <!-- edit-box to edit attachment -->
                            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-field-{{field.id}}" position="left" title="<?php i::esc_attr_e("Editar Campo");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" submit-label="<?php i::esc_attr_e("Salvar");?>" close-on-cancel='true' on-cancel="cancelFieldConfigurationEditBox" on-submit="editFieldConfiguration" index="{{$index}}" spinner-condition="data.fieldSpinner">
                                <label>
                                    <?php i::_e('Nome do campo') ?><br>
                                    <input type="text" ng-model="field.title" placeholder="<?php i::esc_attr_e("Nome do campo");?>"/>
                                </label>
                                <label>
                                <?php i::_e('Descrição do campo') ?><br>
                                    <textarea ng-model="field.description" placeholder="<?php i::esc_attr_e("Descrição do campo");?>"/></textarea>
                                </label>
                                <label>
                                <?php i::_e('Tipo do campo') ?><br>
                                    <select ng-model="field.fieldType" ng-options="value.slug as value.name disable when value.disabled for value in data.fieldTypes" ></select>
                                </label>
                                <?php 
                                foreach($definitions as $def) {
                                    $this->part($def->configTemplate);
                                }
                                ?>
                                
                                <?php $this->part('singles/opportunity-registrations--fields--field-require'); ?>
                                
                                <p ng-if="data.categories.length > 1">
                                    <small><?php i::_e("Selecione em quais categorias este campo é utilizado");?>:</small><br>
                                    <label><input type="checkbox" onclick="if (!this.checked) return false" ng-click="field.categories = []" ng-checked="allCategories(field)"> <?php i::_e("Todas");?> </label>
                                    <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="field.categories" checklist-value="category"> {{category}} </label>
                                </p>
                            </edit-box>



                            <div ng-if="data.entity.canUserModifyRegistrationFields && !isBlockedFields(field.id)" class="btn-group">
                                <a ng-click="openFieldConfigurationEditBox(field.id, $index, $event);" class="btn btn-default edit hltip" title="<?php i::esc_attr_e("editar campo");?>"></a>
                                <a ng-click="removeFieldConfiguration(field.id, $index)" data-href="{{field.deleteUrl}}" class="btn btn-default delete hltip" title="<?php i::esc_attr_e("excluir campo");?>"></a>
                            </div>
                            <div style="color: red;">
                                <strong><em><small>{{ isBlockedFields(field.id) ? '(Campo Bloqueado para edição ou deleção)' : ''}}</small></em></strong>
                            </div>
                            
                            


                        </div>

                            <div ng-if="field.fieldType === 'file'">
                                <div class="js-open-editbox">
                                    <div class="label">{{field.title}} <em><small>({{field.required.toString() === 'true' ? 'Obrigatório' : 'Opcional'}})</small></em></div>

                                    <span ng-if="field.categories.length" class="attachment-description">
                                        <?php i::_e("Somente para");?> <strong>{{field.categories.join(', ')}}</strong>
                                        <br>
                                    </span>
                                    <span class="attachment-description">
                                        <?php i::_e("Tipo");?>: <strong><?php i::_e("Anexo");?></strong>
                                        <br>
                                    </span>
                                    <span class="attachment-description">
                                        <?php i::_e("Descrição");?>: {{field.description}}
                                    </span>
                                </div>
                                <!-- edit-box to edit attachment -->
                                <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-{{field.id}}" position="left" title="<?php i::esc_attr_e("Editar Anexo");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" submit-label="<?php i::esc_attr_e("Salvar");?>" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                                    <input type="text" ng-model="field.title" placeholder="<?php i::esc_attr_e("Nome do anexo");?>"/>
                                    <textarea ng-model="field.description" placeholder="<?php i::esc_attr_e("Descrição do anexo");?>"/></textarea>
                                    <p><label><input type="checkbox" ng-model="field.required" ng-checked="field.required"> <?php i::_e("O envio deste anexo é obrigatório");?></label></p>

                                    <p ng-if="data.categories.length > 1">
                                        <small><?php i::_e("Selecione em quais categorias este anexo é utilizado");?>:</small><br>
                                        <label><input type="checkbox" onclick="if (!this.checked)
                                            return false" ng-click="field.categories = []" ng-checked="allCategories(field)"> <?php i::_e("Todas");?> </label>
                                            <label ng-repeat="category in data.categories"><input type="checkbox" checklist-model="field.categories" checklist-value="category"> {{category}} </label>
                                        </p>
                                    </edit-box>
                                    <div class="file-{{field.template.id}}" ng-if="field.template">
                                        <span ng-if="data.entity.canUserModifyRegistrationFields" class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(field.id, $index, $event);">{{field.template.name}}</span>
                                        <a ng-if="data.entity.canUserModifyRegistrationFields" class="delete hltip" ng-click="removeFileConfigurationTemplate(field.id, $index)" title="<?php i::esc_attr_e("Excluir modelo");?>"></a>
                                    </div>
                                    <p ng-if="!data.entity.canUserModifyRegistrationFields">
                                        <a class="file-{{field.template.id}} attachment-template"  href="{{field.template.url}}" target="_blank" rel='noopener noreferrer'>{{field.template.name}}</a>
                                    </p>
                                    <!-- edit-box to upload attachments -->
                                    <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-template-{{field.id}}" position="top" title="<?php i::esc_attr_e("Enviar modelo");?>" cancel-label="<?php i::esc_attr_e("Cancelar");?>" submit-label="<?php i::esc_attr_e("Enviar modelo");?>" on-submit="sendFile" close-on-cancel='true' spinner-condition="data.uploadSpinner">
                                        <p ng-if="field.template">
                                            <a class="file-{{field.template.id}} attachment-template"  href="{{field.template.url}}" target="_blank" rel='noopener noreferrer'>{{field.template.name}}</a>
                                        </p>
                                        <form class="js-ajax-upload" method="post" data-group="{{uploadFileGroup}}" action="{{getUploadUrl(field.id)}}" enctype="multipart/form-data">
                                            <div class="alert danger hidden"></div>
                                            <p class="form-help"><?php i::_e("Tamanho máximo do arquivo");?>: {{maxUploadSizeFormatted}}</p>
                                            <input type="file" name="{{uploadFileGroup}}" />

                                            <div class="js-ajax-upload-progress">
                                                <div class="progress">
                                                    <div class="bar"></div >
                                                        <div class="percent">0%</div >
                                                        </div>
                                                    </div>

                                                </form>
                                            </edit-box>

                                            
                                            <div ng-if="data.entity.canUserModifyRegistrationFields && !isBlockedFields(field.id)" class="btn-group">
                                                <a ng-click="openFileConfigurationEditBox(field.id, $index, $event);" class="btn btn-default edit hltip" title="<?php i::esc_attr_e("editar anexo");?>"></a>
                                                <a ng-if="!field.template" ng-click="openFileConfigurationTemplateEditBox(field.id, $index, $event);" class="btn btn-default send hltip" title="<?php i::esc_attr_e("enviar modelo");?>" ></a>
                                                <a ng-click="removeFileConfiguration(field.id, $index)" data-href="{{field.deleteUrl}}" class="btn btn-default delete hltip" title="<?php i::esc_attr_e("excluir anexo");?>"></a>
                                            </div>
                                            
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
<!-- #registration-attachments -->
