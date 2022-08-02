<?php
use MapasCulturais\i;
$this->import('popover confirm-button');
?>

<div class="entity-related-agents" v-if="editable || hasGroups()">

    <h3><?php i::_e("Agentes relacionados")?></h3>

    <div v-for="(groupAgents, groupName) in groups" class="entity-related-agents__group">

        <div class="entity-related-agents__group--name">
            <label> {{groupName}} </label> 

            <!-- botões de ação do grupo -->
            <div v-if="editable" class="act">
                <!-- renomear grupo -->
                <popover openside="down-right">
                    <template #button="{ toggle }">
                        <slot name="button" :toggle="toggle"> 
                            <a @click="toggle()"> 
                                <iconify icon="zondicons:edit-pencil"></iconify> 
                            </a>
                        </slot>
                    </template>

                    <template #default="popover">
                        <form @submit="renameGroup(groupName, groupAgents.newGroupName, popover); $event.preventDefault()" class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="col-12">
                                    <input v-model="groupAgents.newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                                </div>
                            </div>
                            
                            <div class="actions">
                                <button class="button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                                <button class="button button--primary"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </popover>
                

                <!-- remover grupo -->
                <confirm-button @confirm="removeGroup(groupName)">
                    <template #button="modal">
                        <a @click="modal.open()"> 
                            <iconify icon="ooui:trash"></iconify> 
                        </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Remover grupo de agentes relacionados?') ?>
                    </template>
                </confirm-button>
            </div>
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
                        <div class="grid-12">
                            <div class="col-12">
                                <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                            </div>
                        </div>
                        
                        <div class="actions">
                            <button class="button button--text" type="reset" @click="close()"> <?php i::_e("Cancelar") ?> </button>
                            <button class="button button--primary"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </div>

            </template>
        </popover>

    </div>

</div>