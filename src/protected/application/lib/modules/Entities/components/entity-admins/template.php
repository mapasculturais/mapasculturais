<?php
use MapasCulturais\i;
?>

<div class="entity-admins" v-if="editable || group.length > 0">

    <h3><?php i::_e("Administradores")?></h3>

    <div class="entity-related-agents__group">
        
        <div class="entity-related-agents__group--agents">

            <div v-for="agent in group" class="agent">
                <a :href="agent.singleUrl" class="agent__img">
                    <img v-if="agent.files.avatar" :src="agent.files.avatar?.transformations?.avatarMedium?.url" class="agent__img--img" />
                    <iconify v-else icon="bi:image-fill" />
                </a>

                <div v-if="editable" class="agent__delete">
                    <!-- remover agente -->
                    <confirm-button @confirm="removeAgent(agent)">
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

            <select-entity type="agent" @select="addAgent($event)" :query="query" openside="down-right">            
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> <?php i::_e('Adicionar administrador') ?> <iconify icon="ps:plus"/> </button>
                </template>
            </select-entity>
            
        </div>
        
    </div>

</div>