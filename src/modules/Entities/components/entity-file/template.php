<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-modal
    mc-icon
    mc-loading
');
?>
<div v-if="file || editable" :class="['entity-file', {'entity-file--disabled' : disabled}, classes]" :data-field="groupName.replace('rfc_', 'file_')">

    <label v-if="title" class="entity-file__title">
        {{title}}
        <span v-if="required" class="required">*<?php i::_e('obrigatório') ?></span>
    </label>
    
    <div v-if="file && hasSlot('label') && !downloadOnly" class="entity-file__label semibold">
        <slot name="label"></slot>
    </div>
    <div v-if="file" class="entity-file__file">

        <slot name="view">
            <a v-if="!downloadOnly" class="entity-file__link primary__color bold" :download="file.name" :href="file.url">
                <mc-icon name="download" :class="entity.__objectType+'__color'"></mc-icon>
                <span v-if="file.name">{{file.name}}</span>
                <span v-else> <? i::_e('Sem descrição') ?> </span>
            </a>

            <a v-if="downloadOnly" class="entity-file__link entity-file__link--download bold" :download="file.name" :href="file.url">
                <span v-if="file.name">{{file.name}}</span>
                <span v-else> <? i::_e('Sem descrição') ?> </span>
                <mc-icon name="download"></mc-icon>
            </a>
        </slot>

        <mc-confirm-button v-if="editable && !required" @confirm="deleteFile(file)">
            <template #button="modal">
                <a @click="modal.open()"> <mc-icon name="trash"></mc-icon> </a>
            </template>

            <template #message="message">
                <?php i::_e('Deseja remover este arquivo?') ?>
            </template>
        </mc-confirm-button>
    </div>

    <mc-modal v-if="editable" :title="titleModal" classes="entity-file__modal">
        <mc-loading :condition="loading"></mc-loading>

        <template v-if="!loading" #default>
            <form @submit="upload(modal); $event.preventDefault();" class="entity-file__newFile">
                <div class="grid-12">
                    <slot name="form" :enableDescription="enableDescription" :disableName="disableName" :formData="formData" :setFile="setFile" :file="newFile">
                        <div class="col-12 field">
                            <label><?php i::_e('Anexe um arquivo') ?></label>
                            <div class="field__upload">
                                <label for="newFile" class="field__buttonUpload button button--icon button--primary-outline">
                                    <mc-icon name="upload"></mc-icon> <?= i::__('Anexar') ?>
                                    <input id="newFile" type="file" @change="setFile($event)" ref="file">
                                    <small><?= i::__('Tamanho máximo do arquivo:') ?> <strong>{{maxFileSize}}</strong></small>
                                </label>
                            </div>
                        </div>

                        <div v-if="!disableName" class="col-12 field">
                            <label><?php i::_e('Título do arquivo') ?></label>
                            <input v-model="newFile.name" type="text" :disabled="groupName == 'rules'"/>
                        </div>

                        <div v-if="enableDescription" class="col-12 field">
                            <label><?php i::_e('Descreva abaixo os motivos do recurso') ?></label>
                            <textarea v-model="formData.description"></textarea>
                        </div>
                    </slot>
                </div>
            </form>
        </template>

        <template v-if="!loading" #button="modal">
            <slot name="button" :open="modal.open" :close="modal.close" :toggle="modal.toggle" :file="file">
                <a v-if="defaultFile" class="entity-file__link entity-file__link--download bold" :download="defaultFile.name" :href="defaultFile.url">
                    <mc-icon name="download"></mc-icon> <?php i::_e("Baixar modelo") ?>
                </a>
                <a v-if="!file" @click="modal.open()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon> <?php i::_e("Enviar") ?>
                </a>
                <a v-if="file" @click="modal.open()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon> <?php i::_e("Atualizar") ?>
                </a>
            </slot>
        </template>

        <template v-if="!loading" #actions="modal">
            <button class="col-6 button button--text" type="reset" @click="modal.close()"> <?php i::_e("Cancelar") ?> </button>
            <button class="col-6 button button--primary" type="submit" @click="upload(modal); $event.preventDefault();"> <?php i::_e("Enviar") ?> </button>
        </template>
    </mc-modal>
</div>