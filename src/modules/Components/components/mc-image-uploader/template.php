<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
');
?>
<div class="mc-image-uploader">
    <mc-modal title="<?php i::_e("Recorte a imagem") ?>" @open="reset()">
        <template #default>
            <div class="field">
                <input v-if="useDescription" v-model="description" class="input" placeholder="<?php i::esc_attr_e('Descrição da imagem') ?>">
            </div>

            <cropper v-if="circular" ref="cropper" :src="image.src" :stencil-props="stencilProps" :stencil-component="$options.components.CircleStencil" :default-size="defaultSize" />
            <cropper v-if="!circular" ref="cropper" :src="image.src" :stencil-props="stencilProps" :default-size="defaultSize" />
        </template>

        <template #button="modal">
            <label>
                <slot :modal="modal" :blob="blob" :file="file" :blobUrl="blobUrl" :description="description" :upload="upload"></slot>
                <input :id="group+<?= date("Ymd") ?>" type="file" ref="file" @change="loadImage($event, modal)" accept="image/*" style="display:none">
            </label>
        </template>
        <template #actions="modal">
            <a class="button button--primary" @click="crop(modal)"><?php i::_e('Recortar e subir imagem') ?></a>
            <a class="button button--secondary" @click="modal.close()"><?php i::_e('Cancelar') ?></a>
        </template>
    </mc-modal>
</div>