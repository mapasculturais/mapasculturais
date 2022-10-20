<?php
use MapasCulturais\i;
$this->import('
    mc-link
    panel--entity-actions
    panel--entity-tabs
');
?>
<panel--entity-tabs :type='type' :user="user.id" :select="select">
    <template #before-list>
        <div class="grid-12 panel--entity-tabs--identify">
            <div class="col-2"><?= i::__('ID') ?></div>
            <div class="col-4"><?= i::__('Nome') ?></div>
            <div class="col-3"><?= i::__('Subsite') ?></div>
            <div class="col-3"><?= i::__('Ações') ?></div>
        </div>
    </template>
    <template #default="{entity, moveEntity}">
        <div class="grid-12 panel--entity-tabs__content">
            <div class="col-2"><code>{{entity.id}}</code></div>
            <div class="col-4"><mc-link :entity="entity"></mc-link></div>
            <div class="col-3">{{entity.subsite?.name}}</div>
            <div class="col-3 panel--entity-tabs__content--editable">
                
                <mc-link :entity="entity" route="edit" icon='edit' class="button button--icon panel--entity-tabs__content--editable-edit"><?= i::__('Editar') ?></mc-link>
                <panel--entity-actions 
                    :entity="entity"
                    @deleted="moveEntity(entity)" 
                    @archived="moveEntity(entity)" 
                    @published="moveEntity(entity)"
                    class="panel__entity-actions--editable"
                    >
                </panel--entity-actions>
            </div>
        </div>
    </template>
</panel--entity-tabs>