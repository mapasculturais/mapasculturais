<div id="registration-attachments" class="registration-fieldset">
    <h4>5. Anexos</h4>
    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Você pode pedir para os proponentes enviarem anexos para se inscrever no seu projeto. Para cada anexo, você pode fornecer um modelo, que o proponente poderá baixar, preencher, e fazer o upload novamente.</p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto. </p>
    <div ng-controller="RegistrationFileConfigurationsController">
        <?php if ($this->controller->action == 'create'): ?>
            <p class="allert warning">Antes de adicionar anexos é preciso salvar o projeto.</p>
        <?php else: ?>
            <p ng-if="data.entity.canUserModifyRegistrationFields" ><a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-files', $event)">Adicionar anexo</a></p>
        <?php endif; ?>
        <!-- edit-box to add attachment -->
        <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files" position="right" title="Adicionar anexo" cancel-label="Cancelar" submit-label="Criar" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
            <input type="text" ng-model="data.newFileConfiguration.title" placeholder="Nome do anexo"/>
            <textarea ng-model="data.newFileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
            <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
        </edit-box>
        <!-- added attachments list -->
        <ul class="attachment-list">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                <div class="js-open-editbox" ng-class="{'mc-editable': data.entity.canUserModifyRegistrationFields}" ng-click="openFileConfigurationEditBox(fileConfiguration.id, $index, $event);">
                    <div class="label">{{fileConfiguration.title}}</div>
                    <span class="attachment-description">{{fileConfiguration.description}} ({{fileConfiguration.required.toString() === 'true' ? 'Obrigatório' : 'Opcional'}})</span>
                </div>
                <!-- edit-box to edit attachment -->
            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-{{fileConfiguration.id}}" position="right" title="Editar Anexo" cancel-label="Cancelar" submit-label="Salvar" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                <input type="text" ng-model="fileConfiguration.title" placeholder="Nome do anexo"/>
                <textarea ng-model="fileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
                <p><label><input type="checkbox" ng-model="fileConfiguration.required" ng-checked="fileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
            </edit-box>
            <div class="file-{{fileConfiguration.template.id}}" ng-if="fileConfiguration.template">
                <span ng-if="data.entity.canUserModifyRegistrationFields" class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);">{{fileConfiguration.template.name}}</span>
                <a ng-if="data.entity.canUserModifyRegistrationFields" class="delete hltip" ng-click="removeFileConfigurationTemplate(fileConfiguration.id, $index)" title="Excluir modelo"></a>
            </div>
            <p ng-if="!data.entity.canUserModifyRegistrationFields">
                <a class="file-{{fileConfiguration.template.id}} attachment-template"  href="{{fileConfiguration.template.url}}" target="_blank">{{fileConfiguration.template.name}}</a>
            </p>
            <!-- edit-box to upload attachments -->
            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-template-{{fileConfiguration.id}}" position="top" title="Enviar modelo" cancel-label="Cancelar" submit-label="Enviar modelo" on-submit="sendFile" close-on-cancel='true' spinner-condition="data.uploadSpinner">
                <p ng-if="fileConfiguration.template">
                    <a class="file-{{fileConfiguration.template.id}} attachment-template"  href="{{fileConfiguration.template.url}}" target="_blank">{{fileConfiguration.template.name}}</a>
                </p>
                <form class="js-ajax-upload" method="post" data-group="{{uploadFileGroup}}" action="{{getUploadUrl(fileConfiguration.id)}}" enctype="multipart/form-data">
                    <div class="alert danger hidden"></div>
                    <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
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
                <a class="btn btn-default send hltip" title="enviar modelo" ng-if="!fileConfiguration.template" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);" >Enviar modelo</a>
                <a data-href="{{fileConfiguration.deleteUrl}}" ng-click="removeFileConfiguration(fileConfiguration.id, $index)" class="btn btn-default delete hltip" title="excluir anexo">Excluir</a>
            </div>
            </li>
        </ul>
    </div>
</div>
<!-- #registration-attachments -->