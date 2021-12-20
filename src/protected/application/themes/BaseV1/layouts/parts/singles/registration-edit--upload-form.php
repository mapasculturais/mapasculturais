<?php
use MapasCulturais\i;

$form_help = $form_help ?? i::__("Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}");
?>
<form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{::field.groupName}}" enctype="multipart/form-data">
    <div class="alert danger hidden"></div>
    <label ng-if="::field.multiple">
        <?php i::_e('Descrição:') ?>
        <input type="input" name="description[{{::field.groupName}}]" />
    </label>
    <p><?php i::_e("Selecione seu anexo:") ?></p>
    <input type="file" name="{{::field.groupName}}" />
    <p class="form-help"><?= $form_help ?></p>
    <div class="js-ajax-upload-progress">
        <div class="progress">
            <div class="bar"></div>
            <div class="percent">0%</div>
        </div>
    </div>
</form>