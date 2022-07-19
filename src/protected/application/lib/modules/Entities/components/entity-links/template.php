<?php 
use MapasCulturais\i;
?>
<div class="entity-links">
    <h4 class="entity-links__title"> {{title}} </h4>

    <ul class="entity-links__links">
        <li class="entity-links__links--item" v-for="link in entity.metalists.links">
            <a class="link" :class="{'editable': editable}" :href="link.value" target="_blank" >
                <iconify icon="eva:link-outline" /> 
                {{link.title}}
            </a>            
            <div v-if="editable" class="edit">
                <a> <iconify icon="zondicons:edit-pencil" /> </a>
                <a> <iconify icon="ooui:trash" /> </a>
            </div>
        </li>
    </ul>

    <popover v-if="editable" openside="down-right">
        <template #button="{ toggle }">
            <slot name="button" :toggle="toggle"> 
                <a class="button button--primary button--icon button--primary-outline" @click="toggle()">
                    <?php i::_e("Adicionar")?>
                    <iconify icon="fluent:add-20-filled"></iconify>
                </a>
            </slot>
        </template>

        <template #default="{ close }">
            <div class="entity-links__newLink">
                <div class="field">
                    <label><?php i::_e('Título do link') ?></label>
                    <input v-model="newLinkTitle" class="newLinkTitle" type="text" name="newLinkTitle" />
                </div>

                <div class="field">
                    <label><?php i::_e('Link') ?></label>
                    <input v-model="newLink" class="newLink" type="text" name="newLink" />
                </div>
                
                <div class="newGroup--actions">
                    <button class="button button--text"  @click="close()"> <?php i::_e("Cancelar") ?> </button>
                    <button @click="addLink(newLinkTitle, newLink)" class="button button--solid"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </div>

        </template>
    </popover>
</div>