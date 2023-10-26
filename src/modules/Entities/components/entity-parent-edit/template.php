<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon
    select-entity
');
?>
<div v-if="parent" :class="['entity-parents-edit' ,classes]">
    <h4 class="entity-parent-edit__title bold">{{entity.name}} {{title}}</h4>
    <a class="entity-parent-edit__parent" :href="parent.singleUrl" :title="parent.shortDescription">
        <div class="entity-parent-edit__parent--img">
            <mc-avatar :entity="parent" size="small"></mc-avatar>
        </div>
        <div class="entity-parent-edit__parent--name">
            <?php i::_e('{{entity.parent.name}}') ?>
        </div>
    </a>
    <div class="entity-parent-edit__edit">
        <select-entity :type="type" @select="changeParent($event)" :query="query" openside="right-down">
            <template #button="{ toggle }">
                <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                    <mc-icon name="exchange"></mc-icon>
                    <h4 v-if="type == 'space'"><?php i::_e( "Trocar supra espaço") ?></h4>
                    <h4 v-if="type == 'project'"><?php i::_e( "Trocar supra projeto") ?></h4>
                </a>
            </template>
        </select-entity>
    </div>
</div>
<div v-if="!parent" class="col-12 entity-parent-edit__edit">
    <select-entity :type="type" @select="changeParent($event)" :query="query" openside="right-down">
        <template #button="{ toggle }">
            <h4 class="title bold"><?php i::_e("{{label}}")?></h4>
            <span v-if="type == 'space'" class="text"><?php i::_e("Selecione um espaço para que o seu espaço atual seja vinculado como integrante") ?></span>
            <span v-if="type == 'project'" class="text"><?php i::_e("Selecione um projeto para que o seu projeto atual seja vinculado como integrante") ?></span>
            <a class="entity-parent-edit__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                <button class="add-button button button--primary-outline  button--icon "><mc-icon class="teste" name="add"></mc-icon><?php i::_e("Adicionar")?></button>
            </a>
        </template>
    </select-entity>
</div>