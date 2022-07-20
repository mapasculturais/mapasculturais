<?php
use MapasCulturais\i;
$this->import('popover confirm-button');
?>

<div class="entity-related-agents" v-if="hasGroups()">

    <h3><?php i::_e("Agentes relacionados")?></h3>

    <div v-for="(groupAgents, groupName) in groups" class="entity-related-agents__group">

        <label class="entity-related-agents__group--name"> {{groupName}} </label>

        <!-- botões de ação do grupo -->
        <div v-if="editable" >
            <!-- renomear grupo -->
            <popover openside="down-right">
                <template #button="{ toggle }">
                    <slot name="button" :toggle="toggle"> 
                        <iconify @click="toggle()"  icon="zondicons:edit-pencil"/> 
                    </slot>
                </template>

                <template #default="popover">
                    <div class="entity-related-agents__addNew--newGroup">
                        <form @submit="renameGroup(groupName, groupAgents.newGroupName, popover); $event.preventDefault()">
                            <input v-model="groupAgents.newGroupName" class="newGroupName" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                            
                            <div class="newGroup--actions">
                                <a class="button button--text"  @click="popover.close()"> <?php i::_e("Cancelar") ?> </a>
                                <button class="button button--primary"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </div>
                </template>
            </popover>
            

            <!-- remover grupo -->
            <confirm-button @confirm="removeGroup(groupName)">
                <template #button="modal">
                    <button @click="modal.open()"class="button button--icon button--text-del button--sm"> <iconify icon="ooui:trash" /> <?php i::_e('Excluir') ?> </button>
                </template> 
                <template #message="message">
                    <?php i::_e('Remover grupo de agentes relacionados?') ?>
                </template>
            </confirm-button>
        </div>

        <!-- lista de agentes -->
        <div class="entity-related-agents__group--agents">
            <div v-for="agent in groupAgents" class="agent">
                <a :href="agent.singleUrl" class="agent__img">
                    <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                    <iconify v-else icon="bi:image-fill" />
                </a>

                <div v-if="editable" class="agent__delete">
                    <!-- remover agente -->
                    <confirm-button @confirm="removeAgent(groupName, agent)">
                        <template #button="modal">
                            <iconify @click="modal.open()" icon="gg:close"/>
                        </template> 
                        <template #message="message">
                            <?php i::_e('Remover agente relacionado?') ?>
                        </template> 
                    </confirm-button>
                </div>
            </div>
            
        </div>
    
        <div class="entity-related-agents__group--actions">

            <select-entity type="agent" @select="addAgent(groupName, $event)" :query="queries[groupName]" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> <?php i::_e('Adicionar agente') ?> <iconify icon="ps:plus"/> </button>
                </template>
            </select-entity>
            
        </div>
        
    </div>


    <div class="entity-related-agents__addNew">

        <popover openside="down-right">
            <template #button="{ toggle }">
                <slot name="button" :toggle="toggle"> 
                    <button class="button button--primary-outline button--icon" @click="toggle()" > <iconify icon="ps:plus"/> <?php i::_e("Adicionar grupo") ?> </button>
                </slot>
            </template>

            <template #default="{ close }">
                <div class="entity-related-agents__addNew--newGroup">
                    <form @submit="addGroup(newGroupName); close(); $event.preventDefault();">
                        <input v-model="newGroupName" class="newGroupName" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                        
                        <div class="newGroup--actions">
                            <a class="button button--text"  @click="close()"> <?php i::_e("Cancelar") ?> </a>
                            <button class="button button--primary"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </div>

            </template>
        </popover>

    </div>

</div>