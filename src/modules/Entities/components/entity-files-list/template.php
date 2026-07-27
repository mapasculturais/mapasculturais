<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field-seals
    mc-confirm-button
    mc-popover 
');
?>
<div :class="classes" v-if="getFiles() || editable" class="files-list">
    <label v-if="!hideTitle && !isDocsAnexoList" class="files-list__title"> {{title}} </label>
    <slot name="description"></slot>

    <ul v-if="getFiles()" class="files-list__list">
        <li class="files-list__list--item" v-for="file in getFiles()">
            <div class="files-list__list--item-main">
                <a
                    class="files-list__list--item-link"
                    :href="file.url"
                    :download="viewAction ? null : file.name"
                    :target="viewAction ? '_blank' : undefined"
                    :rel="viewAction ? 'noopener noreferrer' : undefined">
                    <mc-icon :name="viewAction ? 'file' : 'download'" :class="viewAction ? '' : (entity.__objectType+'__color')"></mc-icon>
                    <span v-if="isDocsAnexoList">{{ title }}</span>
                    <span v-else-if="file.description">{{ file.description }}</span>
                    <span v-else-if="file.name">{{ file.name }}</span>
                    <span v-else> <?php i::_e('Sem descrição') ?> </span>
                </a>
                <entity-field-seals v-if="sealProp" :entity="entity" :prop="sealProp"></entity-field-seals>
            </div>

            <a
                v-if="viewAction && !editable"
                class="files-list__view"
                :href="file.url"
                target="_blank"
                rel="noopener noreferrer">
                <mc-icon name="eye-view"></mc-icon>
                <span>{{ viewActionLabel }}</span>
            </a>

            <div v-if="editable" class="edit" :class="{ 'edit--labeled': labeledActions }">
                <mc-confirm-button @confirm="file.delete()">
                    <template #button="modal">
                        <a class="edit__action edit__action--delete" @click="modal.open()">
                            <mc-icon name="trash"></mc-icon>
                            <span v-if="labeledActions"><?php i::_e('Excluir arquivo') ?></span>
                        </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja remover este arquivo?') ?>
                    </template> 
                </mc-confirm-button>

                <mc-popover @open="file.newDescription = file.description" openside="down-right">
                    <template #button="{toggle}">
                        <a class="edit__action edit__action--edit" @click="toggle">
                            <mc-icon name="edit"></mc-icon>
                            <span v-if="labeledActions"><?php i::_e('Editar título') ?></span>
                        </a>
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
            </div>
        </li>
    </ul>

    <mc-popover v-if="editable" title="<?php i::_e('Adicionar arquivo')?>" openside="down-right">
        <template #button="popover">
            <slot name="button" v-bind="popover"> 
                <a @click="popover.toggle()" :class="['button', 'button--icon', buttonPrimary ? 'button--primary' : 'button--primary button--primary-outline', 'button-up', 'edit-1__portfolio-cta']">
                    <mc-icon :name="buttonPrimary ? 'add' : 'upload'"></mc-icon>
                    {{ buttonLabel || '<?= i::__("Enviar") ?>' }}
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
                            <small><?= i::__('Tamanho máximo do arquivo:') ?> <strong>{{maxFileSize}}</strong></small>
                        </div>
                    </div> 

                    <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>
</div>