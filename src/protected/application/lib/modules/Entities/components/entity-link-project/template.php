<?php

use MapasCulturais\i;

$this->import('
mc-icon
select-entity
');
?>
<div v-if="project" :class="['entity-parents-edit' ,classes]">
    
    <h4 class="entity-parent-edit__title">{{entity.name}} {{title}}</h4>
    <a class="entity-parent-edit__parent" :href="project.singleUrl" :title="project.shortDescription">
        <div class="entity-parent-edit__parent--img">
            <img v-if="project.files.avatar" class="profile" :src="project.files?.avatar?.url">
            <div v-else class="placeholder">
                <mc-icon name="agent-1"></mc-icon>
            </div>
        </div>
        <div class="entity-parent-edit__parent--name">
            <?php i::_e('{{entity.project.name}}') ?>
        </div>
    </a>
    <div class="entity-parent-edit__edit">
        <select-entity :type="type" @select="changeParent($event)" :query="{'@permissions':'createEvents','id':`!EQ(${entity.project.id})`}" openside="right-down">
            <template #button="{ toggle }">
                <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                    <mc-icon name="exchange"></mc-icon>
                    <h4 v-if="type == 'project'"><?php i::_e( "Trocar projeto vinculado ao evento") ?></h4>
                </a>
            </template>
        </select-entity>
    </div>
</div>
<!-- @permissions=createEvent -->
<div v-if="!entity.project" class="col-12 entity-parent-edit__edit">
    <select-entity :type="type" @select="changeProject($event)" :query="{'@permissions':'createEvents'}" openside="right-down">
        <template #button="{ toggle }">
            <h4 class="title"><?php i::_e("{{label}}")?></h4>
            <span v-if="type == 'project'" class="text"><?php i::_e("Selecione um projeto para que o seu evento seja vinculado como integrante") ?></span>
            <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                <button class="add-button button button--primary-outline  button--icon "><mc-icon class="teste" name="add"></mc-icon><?php i::_e("Adicionar")?></button>
            </a>
        </template>
    </select-entity>
</div>