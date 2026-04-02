<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('mc-icon');
?>

<div class="oc-upload">
    <form @submit.prevent="upload()" class="entity-file__newFile">
        <label for="newFile" class="upload-label">
            <div
                class="upload-preview"
                v-if="previewImage"
                :style="{ backgroundImage: `url(${previewImage})` }">
            </div>
            <div class="upload-content">
                <mc-icon name="one-click-upload"></mc-icon><br>
                <p><?= i::__('Importe seu arquivo') ?></p>
                <p><?= i::__('Arraste ou clique para fazer upload') ?></p>
            </div>
            <input id="newFile" type="file" @change="setFile($event)" ref="file" />
        </label>
    </form>

    <div class="reset-values">
        <oc-reset-default-values :entity="entity" :prop="prop"></oc-reset-default-values>
    </div>

</div>