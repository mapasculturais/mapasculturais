<?php
use MapasCulturais\i;
$this->import('popover confirm-button');
?>

<div :class="classes" class="entity-related-agents" v-if="editable || hasGroups()">

    <h3><?php i::_e("Agentes relacionados")?></h3>

    <div v-for="(groupAgents, groupName) in groups" class="entity-related-agents__group">

        <div class="entity-related-agents__group--name">
            <label> {{groupName}} </label> 

            <!-- botões de ação do grupo -->
            <div v-if="editable" class="act">
                <!-- renomear grupo -->
                <popover openside="down-right">
                    <template #button="popover">
                        <slot name="button"> 
                            <a @click="popover.toggle()"> <mc-icon name="edit"></mc-icon> </a>
                        </slot>
                    </template>

                    <template #default="{popover, close}">
                        <form @submit="renameGroup(groupName, groupAgents.newGroupName, popover); $event.preventDefault(); close()" class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="col-12">
                                    <input v-model="groupAgents.newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                                </div>

                                <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </popover>
                

                <!-- remover grupo -->
                <confirm-button @confirm="removeGroup(groupName)">
                    <template #button="modal">
                        <a @click="modal.open()"> 
                            <mc-icon name="trash"></mc-icon>
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
                    <mc-icon v-else name="agent"></mc-icon>
                </a>

                <div v-if="editable" class="agent__delete">
                    <!-- remover agente -->
                    <confirm-button @confirm="removeAgent(groupName, agent)">
                        <template #button="modal">
                            <mc-icon @click="modal.open()" name="delete"></mc-icon>
                        </template> 
                        <template #message="message">
                            <?php i::_e('Remover agente relacionado?') ?>
                        </template> 
                    </confirm-button>
                </div>
            </div>
            
        </div>
    
        <div v-if="editable" class="entity-related-agents__group--actions">
            <select-entity type="agent" @select="addAgent(groupName, $event)" :query="queries[groupName]" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> 
                        <?php i::_e('Adicionar agente') ?> 
                        <mc-icon name="add"></mc-icon>
                    </button>
                </template>
            </select-entity>            
        </div>
        
    </div>


    <div v-if="editable" class="entity-related-agents__addNew">

        <popover openside="down-right">
            <template #button="popover">
                <slot name="button"> 
                    <button @click="popover.toggle()" class="button button--primary-outline button--icon"> 
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Adicionar grupo") ?> 
                    </button>
                </slot>
            </template>

            <template #default="{close}">
                <div class="entity-related-agents__addNew--newGroup">
                    <form @submit="addGroup(newGroupName); $event.preventDefault(); close();">
                        <div class="grid-12">
                            <div class="col-12">
                                <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                            </div>

                            <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                            <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </div>

            </template>
        </popover>

    </div>

</div>