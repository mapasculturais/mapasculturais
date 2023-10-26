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
<div v-if="project" :class="['entity-link-project' ,classes]">
    
    <h4 class="entity-link-project__title bold">{{entity.name}} {{title}}</h4>
    <a class="entity-link-project__project" :href="project.singleUrl" :title="project.shortDescription">
       <mc-avatar :entity="project" size="small"></mc-avatar>
        <div class="entity-link-project__project--name">
            <?php i::_e('{{entity.project.name}}') ?>
        </div>
    </a>
    <div class="entity-link-project__edit">
        <select-entity :type="type" @select="changeProject($event)" :query="{'@permissions':'createEvents','id':`!EQ(${entity.project.id})`}" openside="right-down">
            <template #button="{ toggle }">
                <a class="entity-link-project__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                    <mc-icon name="exchange"></mc-icon>
                    <h4 v-if="type == 'project'"><?php i::_e( "Trocar projeto vinculado ao evento") ?></h4>
                </a>
            </template>
        </select-entity>
    </div>
</div>
<!-- @permissions=createEvent -->
<div v-if="!entity.project" class="col-12 entity-link-project__edit">
    <select-entity :type="type" @select="changeProject($event)"  :query="{'@permissions':'createEvents'}" openside="right-down">
        <template #button="{ toggle }">
            <h4 class="title"><?php i::_e("{{label}}")?></h4>
            <span v-if="type == 'project'" class="text"><?php i::_e("Selecione um projeto a ser vinculado ao evento") ?></span>
            <a class="entity-link-project__edit--btn" :class="this.entity.__objectType + '__color'" @click="toggle()">
                <button class="add-button button button--primary-outline  button--icon "><mc-icon class="teste" name="add"></mc-icon><?php i::_e("Adicionar")?></button>
            </a>
        </template>
    </select-entity>
</div>