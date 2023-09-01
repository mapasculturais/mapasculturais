<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-popover
');
?>
<div :class="classes" v-if="file || editable" class="opportunity-rules">
    <label class="opportunity-rules__title">
        {{title}}
        <span v-if="required" class="required">*<?php i::_e('obrigatório') ?></span>
    </label>

    <ul v-if="file" class="opportunity-rules__list">
        <li class="opportunity-rules__list--item">
            <slot name="view">
                <a class="opportunity-rules__list--item-link" :download="file.name" :href="file.url">
                    <mc-icon name="download" :class="entity.__objectType+'__color'"></mc-icon>
                    <span v-if="file.name">{{file.name}}</span>
                    <span v-else> <? i::_e('Sem descrição') ?> </span>
                </a>
            </slot>
            <div v-if="editable && !required" class="edit">
                <mc-confirm-button @confirm="file.delete()">
                    <template #button="modal">
                        <a @click="modal.open()"> <mc-icon name="trash"></mc-icon> </a>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja remover este arquivo?') ?>
                    </template>
                </mc-confirm-button>
            </div>
        </li>
    </ul>

    <mc-popover v-if="editable" title="<?php i::_e('Adicionar arquivo') ?>" openside="down-right">
        <template #button="popover">
            <slot name="button">
                <a v-if="!file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon>
                    <?php i::_e("Enviar") ?>
                </a>

                <a v-if="file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon>
                    <?php i::_e("Atualizar") ?>
                </a>
            </slot>
        </template>

        <template #default="popover">
            <form @submit="upload(popover); $event.preventDefault();" class="entity-files__newFile">

                <div class="grid-12">
                    <slot name="form">
                        <div v-if="!disableName" class="col-12">
                            <label><?php i::_e('Título do arquivo') ?></label>
                            <input v-model="formData.name" type="text" />
                        </div>
                        <div v-if="enableDescription" class="col-12">
                            <label><?php i::_e('Descrição do arquivo') ?></label>
                            <textarea v-model="formData.description"></textarea>
                        </div>
                    </slot>
                    <div class="col-12">
                        <slot name="edit">
                            <div class="field">
                                <label><?php i::_e('Arquivo') ?></label>
                                <input type="file" @change="setFile" ref="file">
                            </div>
                        </slot>
                    </div>
                    <button class="col-6 button button--text" type="reset" @click="popover.close"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>
</div>