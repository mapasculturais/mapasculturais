<?php
use MapasCulturais\i;

$this->import('select-entity confirm-button');
?>

<div class="entity-related-agents" v-if="editable || group.length > 0">

    <h3><?php i::_e("Administradores")?></h3>

    <div class="entity-related-agents__group">
        
        <div class="entity-related-agents__group--agents">

            <div v-for="agent in group" class="agent">
                <a :href="agent.singleUrl" class="agent__img">
                    <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                    <mc-icon v-else name="agent"></mc-icon>
                </a>

                <div v-if="editable" class="agent__delete">
                    <!-- remover agente -->
                    <confirm-button @confirm="removeAgent(agent)">
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
    
        <div class="entity-related-agents__group--actions">

            <select-entity v-if="editable" type="agent" @select="addAgent($event)" :query="query" openside="down-right">            
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> 
                        <?php i::_e('Adicionar administrador') ?> 
                        <mc-icon name="add"></mc-icon> 
                    </button>
                </template>
            </select-entity>
            
        </div>
        
    </div>

</div>