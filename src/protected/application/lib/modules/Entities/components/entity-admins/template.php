<?php
use MapasCulturais\i;
?>

<div class="entity-admins" v-if="hasGroups()">

    <h3><?php i::_e("Grupo de administradores")?></h3>

    <div v-for="(groupAgents, groupName) in group" class="entity-related-agents__group">
        
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
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()"> <?php i::_e('Adicionar administrador') ?> <iconify icon="ps:plus"/> </button>
                </template>
            </select-entity>
            
        </div>
        
    </div>

</div>