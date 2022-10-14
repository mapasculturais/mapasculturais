<?php

use MapasCulturais\i;

$this->import('select-entity');
?>
<div v-if="parent" class="entity-parent">
    <h4><?php i::_e('{{entity.name}}')?> <?php i::_e('{{title}}') ?></h4>
    <a class="entity-parent__parent" :href="parent.singleUrl" :title="parent.shortDescription">
        <div class="entity-parent__parent--img">
            <img v-if="parent.files.avatar" class="profile" :src="parent.files?.avatar?.url">
            <div v-else class="placeholder">
                <mc-icon name="agent-1"></mc-icon>
            </div>
        </div>
        <div class="entity-parent__parent--name">
        <?php i::_e('{{entity.parent.name}}') ?>
        </div>
    </a>

    <div class="entity-parent__edit">
        
        <select-entity :type="type" @select="changeParent($event)" openside="right-down">            
            <template #button="{ toggle }">
                <a class="entity-parent__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()"> 
                    <mc-icon name="exchange"></mc-icon>
                    <h4><?php i::_e('Alterar Propriedade') ?></h4>    
                </a>
            </template>
        </select-entity>

    </div>

</div>