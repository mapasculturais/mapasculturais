<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Campos adicionais");?></h4>
    <p class="registration-help"><?php \MapasCulturais\i::_e("Para efetuar sua inscrição, informe os campos abaixo.");?></p>
    <ul class="attachment-list" ng-controller="RegistrationFieldsController"> 
        <li ng-repeat="field in data.fields" ng-if="showFieldForCategory(field)" on-repeat-done="registration-fields" class="attachment-list-item registration-edit-mode">
            <div ng-show="field.fieldType !== 'file'" id="registration-field-{{field.id}}" >
                <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

                <div ng-if="field.description" class="attachment-description">{{field.description}}</div>

                <p ng-if="field.fieldType === 'textarea'" style="position: relative;">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="textarea" data-tpl="<textarea onkeyup='charCounter(this);' maxlength='{{ !field.maxSize ?'': field.maxSize }}'></textarea><span id='charCounter'></span>" data-original-title="{{field.title}}" data-maxlength="{{ field.maxSize }}"  data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
                </p>
                <p ng-if="field.fieldType === 'text'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="text" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
                </p>

                <p ng-if="field.fieldType === 'date'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-viewformat="dd-mm-yyyy" data-name="{{field.fieldName}}" data-type="date" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}"></span>
                </p>

                <p ng-if="field.fieldType === 'url'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="url" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
                </p>

                <p ng-if="field.fieldType === 'email'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="email" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
                </p>

                <p ng-if="field.fieldType === 'select'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="select" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}">{{entity[field.fieldName]}}</span>
                </p>

                <p ng-if="field.fieldType === 'checkboxes'">
                    <span class='js-editable-field js-include-editable' id="{{field.fieldName}}" data-name="{{field.fieldName}}" data-type="checklist" data-original-title="{{field.title}}" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe");?>" data-value="{{entity[field.fieldName]}}" style="white-space: pre;">{{entity[field.fieldName].join("\n")}}</span>
                </p>
            </div>


            <div ng-show="field.fieldType === 'file'" id="registration-file-{{field.id}}" >
                <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>
                <div class="attachment-description">
                    <span ng-if="field.description">{{field.description}}<span>
                    <span ng-if="field.template">
                        (<a class="attachment-template" target="_blank" href="{{field.template.url}}"><?php \MapasCulturais\i::_e("baixar modelo");?></a>)
                    </span>
                </div>
                <a ng-if="field.file" class="attachment-title" href="{{field.file.url}}" target="_blank">{{field.file.name}}</a>

                <div class="btn-group">
                    <!-- se já subiu o arquivo-->
                    <!-- se não subiu ainda -->
                    <a class="btn btn-default hltip" ng-class="{'send':!field.file,'edit':field.file}" ng-click="openFileEditBox(field.id, $index, $event)" title="{{!field.file ? 'enviar' : 'editar'}} <?php \MapasCulturais\i::_e("anexo");?>">{{!field.file ? 'Enviar' : 'Editar'}}</a>
                    <a class="btn btn-default delete hltip" ng-if="!field.required && field.file" ng-click="removeFile(field.id, $index)" title="<?php \MapasCulturais\i::esc_attr_e("excluir anexo");?>"><?php \MapasCulturais\i::_e("Excluir");?></a>
                </div>

                <edit-box id="editbox-file-{{field.id}}" position="bottom" title="{{field.title}} {{field.required ? '*' : ''}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' on-submit="sendFile" submit-label="<?php \MapasCulturais\i::esc_attr_e("Enviar anexo");?>" index="{{$index}}" spinner-condition="data.uploadSpinner">
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