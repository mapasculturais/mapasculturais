<?php 
use MapasCulturais\i;

$this->import('modal'); 
?>

<div class="upload-example">
    
    <modal title="<?php i::_e("Recorte a imagem") ?>">
        <template #default>
            <div style="height:500px">
                <cropper
                    ref="cropper"
                    class="upload-example-cropper"
                    :src="image.src"
                    :stencil-props="stencilProps"
                    :default-size="defaultSize"
                />
            </div>
        </template>

        <template #button="modal">
            <input type="file" ref="file" @change="loadImage($event, modal)" accept="image/*">
        </template>

        <template #actions="modal">
            <a class="button button--primary" @click="crop(modal)"><?php i::_e('Enviar Imagem') ?></a>
            <a class="button button--secondary" @click="modal.close()"><?php i::_e('Cancelar') ?></a>
        </template>
    </modal>
</div>
