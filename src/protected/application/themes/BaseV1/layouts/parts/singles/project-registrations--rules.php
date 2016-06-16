<p ng-if="!data.isEditable && data.entity.registrationRulesFile"><a class="btn btn-default download" href="{{data.entity.registrationRulesFile.url}}" >Baixar o regulamento</a></p>
<div ng-if="data.isEditable" class="registration-fieldset">
    <h4>2. Regulamento</h4>

    <?php if ($this->controller->action == 'create'): ?>
        <p class="allert warning">Antes de subir o regulamento é preciso salvar o projeto.</p>

    <?php else: ?>
        <p class="registration-help">Envie um arquivo com o regulamento. Formatos aceitos .doc, .odt e .pdf.</p>
        <a class="btn btn-default send hltip" ng-if="!data.entity.registrationRulesFile" ng-click="openRulesUploadEditbox($event)" title="Enviar regulamento" >Enviar</a>
        <div ng-if="data.entity.registrationRulesFile">
            <span class="js-open-editbox mc-editable" ng-click="openRulesUploadEditbox($event)">{{data.entity.registrationRulesFile.name}}</span>
            <a class="delete hltip" ng-click="removeRegistrationRulesFile()" title="excluir regulamento"></a>
        </div>
        <edit-box id="edibox-upload-rules" position="bottom" title="Regulamento" submit-label="Enviar" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendRegistrationRulesFile" on-cancel="closeRegistrationRulesUploadEditbox" spinner-condition="data.uploadSpinner">
            <form class="js-ajax-upload" method="post" action="<?php echo $app->createUrl('project', 'upload', array($entity->id)) ?>" data-group="rules"  enctype="multipart/form-data">
                <div class="alert danger hidden"></div>
                <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
                <input type="file" name="rules" />

                <div class="js-ajax-upload-progress">
                    <div class="progress">
                        <div class="bar"></div>
                        <div class="percent">0%</div>
                    </div>
                </div>
            </form>
        </edit-box>
    <?php endif ?>
</div>
<!-- #registration-rules -->