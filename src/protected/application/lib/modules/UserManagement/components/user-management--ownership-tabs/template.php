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
        <div class="grid-12">
            <div class="col-2"><?= i::__('ID') ?></div>
            <div class="col-4"><?= i::__('Nome') ?></div>
            <div class="col-3"><?= i::__('Subsite') ?></div>
            <div class="col-3"><?= i::__('Ações') ?></div>
        </div>
    </template>
    <template #default="{entity, moveEntity}">
        <div class="grid-12">
            <div class="col-2"><code>{{entity.id}}</code></div>
            <div class="col-4"><mc-link :entity="entity"></mc-link></div>
            <div class="col-3">{{entity.subsite?.name}}</div>
            <div class="col-3">
                <mc-link :entity="entity" route="edit" icon='edit' class="button button--icon"><?= i::__('Editar') ?></mc-link>
                <panel--entity-actions 
                    :entity="entity"
                    @deleted="moveEntity(entity)" 
                    @archived="moveEntity(entity)" 
                    @published="moveEntity(entity)"
                >
                </panel--entity-actions>
            </div>
        </div>
    </template>
</panel--entity-tabs>