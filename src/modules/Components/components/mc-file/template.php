<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="mc-file">
    <h5 class="bold">
        <?= i::__('Anexar:') ?>
    </h5>

    <label :for="uniqueId" class="mc-file__field">
        <span class="button button--primary-outline button--icon">
            <?= i::__('Escolher arquivo') ?> <mc-icon name="attachment"></mc-icon>
        </span>
        <h5 :class="['semibold', {'primary__color' : newFile}]"> {{fileName}} </h5>
        <input :id="uniqueId" type="file" name="newFile" class="mc-file__input" @change="setFile($event)" rel="newFile">
    </label>

    <small class="mc-file__field-rules">Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
</div>