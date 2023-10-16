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
<div :class="classes" v-if="getFiles() || editable" class="files-list">
    <label class="files-list__title"> {{title}} </label>

    <ul v-if="getFiles()" class="files-list__list">
        <li class="files-list__list--item" v-for="file in getFiles()">
            <a class="files-list__list--item-link" :download="file.name" :href="file.url">
                <mc-icon name="download" :class="entity.__objectType+'__color'"></mc-icon>
                <span v-if="file.description">{{file.description}}</span>
                <span v-else> <? i::_e('Sem descrição') ?> </span>
            </a>

            <div v-if="editable" class="edit">
                <mc-popover @open="file.newDescription = file.description" openside="down-right">
                    <template #button="{toggle}">
                        <a @click="toggle"> <mc-icon name="edit"></mc-icon> </a>
                    </template>
                    <template #default="{popover, close}">
                        <form @submit="rename(file, popover); $event.preventDefault()" class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="col-12">                                    
                                    <div class="field">
                                        <label><?php i::_e('Título do arquivo') ?></label>
                                        <input class="input" v-model="file.newDescription" type="text" placeholder="<?php i::esc_attr_e("Informe o título do arquivo") ?>"/>
                                    </div>
                                </div>

                                <button class="col-6 button button--text button-files" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
                
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
                <a @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                    <mc-icon name="upload"></mc-icon>
                    <?php i::_e("Enviar")?>
                </a>
            </slot>
        </template>

        <template #default="popover">
            <form @submit="upload(popover); $event.preventDefault();" class="entity-files__newFile">
                <div class="grid-12">
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Título do arquivo') ?></label>
                            <input v-model="newFile.description" class="newFileDescription" type="text" name="newFileDescription" />
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Arquivo') ?></label>
                            <input type="file" @change="setFile" ref="file"> 
                            <small>Tamanho máximo do arquivo: <strong>{{maxFileSize}}</strong></small>
                        </div>
                    </div> 

                    <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>
</div>