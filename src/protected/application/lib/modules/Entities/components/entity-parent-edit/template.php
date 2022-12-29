<?php

use MapasCulturais\i;

$this->import('select-entity');
?>
<div v-if="parent" :class="['entity-parentss-edit' ,classes]">
    <h4 class="entity-parentss-edit__title">{{entity.name}} {{title}}</h4>
    <a class="entity-parent-edit__parent" :href="parent.singleUrl" :title="parent.shortDescription">
        <div class="entity-parent-edit__parent--img">
            <img v-if="parent.files.avatar" class="profile" :src="parent.files?.avatar?.url">
            <div v-else class="placeholder">
                <mc-icon name="agent-1"></mc-icon>
            </div>
        </div>
        <div class="entity-parent-edit__parent--name">
        <?php i::_e('{{entity.parent.name}}') ?>
        </div>
    </a>
    <div class="entity-parent-edit__edit">        
        <select-entity :type="type" @select="changeParent($event)" openside="right-down">            
            <template #button="{ toggle }">
                <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()"> 
                    <mc-icon name="exchange"></mc-icon>
                    <h4><?php i::_e('Alterar Supraespaço') ?></h4>    
                </a>
            </template>
        </select-entity>
    </div>
</div>
<div v-if="!parent">
    <select-entity :type="type" @select="changeParent($event)" openside="right-down">            
        <template #button="{ toggle }">
            <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()"> 
                <button class="button button--primary-outline  button--icon">Adicionar Supraespaço</button>
            </a>
        </template>
    </select-entity>
</div>