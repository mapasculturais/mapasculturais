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

<div :class="['event-importer-upload', classes]">
    <div class="grid-12 v-center">
        <div class="col-3 sm:col-12">    
            <mc-modal :title="modalTitle">
                <template v-if="!loading" #default>
                    <p><?php i::_e('Para importar eventos, faça upload da planilha de modelo preechida') ?></p>
                    
                    <div class="event-importer-upload__field">
                        <p id="fileName">{{fileName}}</p>
                        <label for="fileUpload">
                            <small class="input-label semibold">
                                <?= i::__("Selecionar arquivo") ?> 
                                <mc-icon name="add"></mc-icon>
                            </small>
                        </label>
                        <div class="field">
                            <input type="file" name="file" id="fileUpload" @change="setFile" ref="file">
                            <small>Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
                        </div>
                    </div>
                </template>

                <template v-if="loading" #default>
                    <p class="event-importer-upload__loading semibold">
                        <?= i::__("Carregando") ?> <mc-icon name="loading"></mc-icon>
                    </p>
                </template>
        
                <template #button="modal">
                    <button class="button button--primary-outline button--large" @click="modal.open()"><?php i::_e('Importar Evento') ?></button>
                </template>
        
                <template #actions="modal">
                    <div class="grid-12">
                        <div class="col-6">
                            <button class="button button--text button--large button--md" @click="cancel(modal)"><?php i::_e('Cancelar') ?></button>
                        </div>
                        <div class="col-6">
                            <button class="button button--primary button--large button--md" @click="upload(modal)"><?php i::_e('Enviar') ?></button>
                        </div>
                    </div>
                </template>
            </mc-modal>
        </div>
        <div class="col-3 sm:col-12">    
            <a :href="csvUrl" class="button button--icon button--large primary__color"> 
                <?= i::__('Baixar modelo CSV') ?> <mc-icon name="download"></mc-icon>
            </a>
        </div>
        <div class="col-3 sm:col-12">    
            <a :href="xlsUrl" class="button button--icon button--large primary__color"> 
                <?= i::__('Baixar modelo XLS') ?> <mc-icon name="download"></mc-icon>
            </a>
        </div>
    </div>
</div>