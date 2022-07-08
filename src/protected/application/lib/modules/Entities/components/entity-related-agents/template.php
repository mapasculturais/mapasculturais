<?php
use MapasCulturais\i;
$this->import('popover');
?>

<div class="entity-related-agents" v-if="hasGroups()">

    <h3><?php i::_e("Grupo de agentes")?></h3>

    {{groups}}

    <div v-for="(groupAgents, groupName) in entity.relatedAgents" class="entity-related-agents__group">

        <label class="entity-related-agents__group--name"> {{groupName}} </label>
        
        <div class="entity-related-agents__group--agents">

            <div v-for="agent in groupAgents" class="agent">
                <a :href="agent.singleUrl" class="agent__img">
                    <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                    <iconify v-else icon="bi:image-fill" />
                </a>

                <div v-if="editable" class="agent__delete">
                    <iconify icon="gg:close"/>
                </div>
            </div>
            
        </div>
    
        <div class="entity-related-agents__group--actions">

            <select-entity type="agent" @select="changeOwner($event)" openside="down-right">            
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> <?php i::_e('Adicionar agente') ?> <iconify icon="ps:plus"/> </button>
                </template>
            </select-entity>

            <button class="button button--icon button--text-del button--sm"> <iconify icon="ooui:trash" /> <?php i::_e('Excluir') ?> </button>
        </div>
        
    </div>


    <div class="entity-related-agents__addNew">

        <label class="entity-related-agents__addNew--title"> <?php i::_e("Adicionar novo grupo de agentes") ?> </label>

        <popover openside="down-right">
            <template #button="{ toggle }">
                <slot name="button" :toggle="toggle"> 
                    <button class="button button--primary-outline button--icon" @click="toggle()" > <iconify icon="ps:plus"/> <?php i::_e("Adicionar") ?> </button>
                </slot>
            </template>

            <template #default="{ close }">
                <div class="entity-related-agents__addNew--newGroup">
                    <input class="newGroupName" type="text" name="newGroup" placeholder="Digite o nome do grupo" />
                    
                    <div class="newGroup--actions">
                        <button class="button button--text"  @click="close()"> <?php i::_e("Cancelar") ?> </button>
                        <button class="button button--solid"> <?php i::_e("Confirmar") ?> </button>
                    </div>
                </div>

            </template>
        </popover>

    </div>

</div>