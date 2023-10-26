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
<div v-if="entity.metalists.links || editable" :class="['entity-links', classes]">
    <label class="entity-links__title"> {{title}} </label>

    <ul v-if="entity.metalists.links" class="entity-links__links">
        <li class="entity-links__links--item" v-for="metalist in entity.metalists.links">
            <a class="link" :class="{'editable': editable}" :href="metalist.value" target="_blank" >
                <mc-icon name="link"></mc-icon> 
                {{metalist.title}}
            </a>            
            <div v-if="editable" class="edit">
                <mc-popover openside="down-right" title="<?php i::_e('Editar link')?>">
                    <template #button="popover">
                        <a @click="metalist.newData = {...metalist}; popover.toggle()"> <mc-icon name="edit"></mc-icon> </a>
                    </template>
                    <template #default="popover">
                        <form @submit="save(metalist, popover); $event.preventDefault(); " class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="col-12">
                                    <div class="field">
                                        <label><?php i::_e('Título do link') ?></label>
                                        <input class="input" v-model="metalist.newData.title" type="text" />
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="field">
                                        <label><?php i::_e('Link') ?></label>
                                        <input class="input" v-model="metalist.newData.value" type="url" />
                                    </div>
                                </div>

                                <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
                
                <mc-confirm-button @confirm="metalist.delete()">
                    <template #button="{open}">
                        <a @click="open()"> <mc-icon name="trash"></mc-icon> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja remover o link?') ?>
                    </template> 
                </mc-confirm-button>
                
            </div>
        </li>
    </ul>

    <mc-popover v-if="editable" openside="down-right" title="<?php i::esc_attr_e('Adicionar Link')?>">
        <template #button="popover">
            <a @click="popover.toggle()" class="button button--primary button--icon button--primary-outline">
                <mc-icon name="add"></mc-icon>
                <?php i::_e("Adicionar Link")?>
            </a>
        </template>

        <template #default="popover">
            <form @submit="create(popover); $event.preventDefault();" class="entity-links__newLink">
                <div class="grid-12">
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Título do link') ?></label>
                            <input v-model="metalist.title" class="newLinkTitle" type="text" name="newLinkTitle" />
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Link') ?></label>
                            <input v-model="metalist.value" class="newLink" type="url" name="newLink" />
                        </div>
                    </div> 

                    <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--solid" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover>
</div>