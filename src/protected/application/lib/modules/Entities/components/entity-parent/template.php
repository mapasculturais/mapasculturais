<?php

use MapasCulturais\i;

$this->import('select-entity');
?>
<div v-if="owner" class="entity-owner">
    <h4>{{entity.name}} {{title}}</h4>
    <a class="entity-owner__owner" :href="owner.singleUrl" :title="owner.shortDescription">
        <div class="entity-owner__owner--img">
            <img v-if="owner.files.avatar" class="profile" :src="owner.files?.avatar?.url">
            <div v-else class="placeholder">
                <mc-icon name="agent-1"></mc-icon>
            </div>
        </div>
        <div class="entity-owner__owner--name">
            {{entity.parent.name}}
        </div>
    </a>

    <div v-if="editable" class="entity-owner__edit">
        
        <select-entity type="agent" @select="changeOwner($event)" openside="right-down">            
            <template #button="{ toggle }">
                <a class="entity-owner__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()"> 
                    <mc-icon name="exchange"></mc-icon>
                    <h4><?php i::_e('Alterar Propriedade') ?></h4>    
                </a>
            </template>
        </select-entity>

    </div>

</div>