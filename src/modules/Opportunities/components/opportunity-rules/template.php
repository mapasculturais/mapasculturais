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
    <label class="opportunity-rules__title"> {{title}} </label>

    <ul v-if="file" class="opportunity-rules__list">
        <li class="opportunity-rules__list--item">
            <a class="opportunity-rules__list--item-link" :download="file.name" :href="file.url">
                <mc-icon name="download" :class="entity.__objectType+'__color'"></mc-icon>
                <span v-if="file.name">{{file.name}}</span>
                <span v-else> <? i::_e('Sem descrição') ?> </span>
            </a>

            <div v-if="editable" class="edit">                
                <mc-confirm-button @confirm="file.delete()">
                    <template #button="modal">
                        <a @click="modal.open()"> <mc-icon name="trash"></mc-icon> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja remover o link?') ?>
                    </template> 
                </mc-confirm-button>                
            </div>
        </li>
    </ul>

    <mc-popover v-if="editable" title="<?php i::_e('Adicionar arquivo')?>" openside="down-right">
        <template #button="popover">
            <slot name="button"> 
                <a v-if="!file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon>
                    <?php i::_e("Enviar")?>
                </a>

                <a v-if="file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon>
                    <?php i::_e("Atualizar")?>
                </a>
            </slot>
        </template>

        <template #default="{popover, close}">
            <form @submit="upload(popover); $event.preventDefault();" class="entity-files__newFile">
                <div class="grid-12">                    
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Arquivo') ?></label>
                            <input type="file" @change="setFile" ref="file"> 
                            <small>Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
                        </div>
                    </div> 

                    <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>
</div>