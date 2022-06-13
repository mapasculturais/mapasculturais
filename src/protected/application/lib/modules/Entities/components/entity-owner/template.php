<?php
$this->import('select-entity');
?>
<div v-if="owner" class="entity-owner">
    <h4>{{title}}</h4>
    <a class="entity-owner__owner" :href="owner.singleUrl" :title="owner.shortDescription">
        <div class="entity-owner__owner--img">
            <img v-if="owner.files" class="profile" :src="owner.files?.avatar?.url">
            <div v-else class="placeholder">
                <iconify icon="bi:image-fill" />
            </div>
        </div>
        <div class="entity-owner__owner--name">
            {{owner.name}}
        </div>
    </a>

    <div v-if="editable" class="entity-owner__edit">
        
        <select-entity type="agent" @select="changeOwner($event)">            
            <template #btn="{ toggle }">
                <a class="entity-owner__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()"> 
                    <iconify icon="material-symbols:change-circle-outline"/> <h4> Alterar Propriedade</h4>    
                </a>
            </template>
        </select-entity>

    </div>

</div>