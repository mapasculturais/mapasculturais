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

    <label :for="propId" class="mc-file__field">
        <span class="button button--primary-outline button--icon">
            <?= i::__('Escolher arquivo') ?> <mc-icon name="attachment"></mc-icon>
        </span>
        <h5 :class="['semibold', {'primary__color' : newFile}]"> {{fileName}} </h5>
        <input type="file" :id="propId" class="mc-file__input" rel="newFile" name="newFile" :accept="accept" @change="setFile($event)">
    </label>

    <small class="mc-file__field-rules">Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
</div>