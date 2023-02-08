<?php

use MapasCulturais\i;

$this->import('
    mc-link
    panel--entity-actions
    panel--entity-tabs
');
?>
<panel--entity-tabs tabs="publish,draft,trash,archived" :type='type' :user="user.id" :select="select">
    <!-- <template #list="{entity, moveEntity}">
        <div style="display:table; width:100%;">

            <div style="display:table-row" class="panel--entity-tabs__content">
                <div  style="display:table-cell; width:10%;" class=" panel--entity-tabs__content--id"><code>{{entity.id}}</code></div>
                <div  style="display:table-cell; width:25%;" class=" panel--entity-tabs__content--name"><mc-link :entity="entity"></mc-link></div>
                <div  style="display:table-cell; width:25%;" class=" panel--entity-tabs__content--pen">{{entity.subsite?.name}}</div>
                <div  style="display:table-cell; width:40%;" class="  panel--entity-tabs__content--editable">
                    <mc-link :entity="entity" route="edit" icon='edit' class="panel--entity-tabs__content--editable-edit button button--sm">
                        <span class="panel--entity-tabs__content--editable-label"><?= i::__('Editar') ?> </span>
                    </mc-link>
                    <panel--entity-actions :entity="entity" @deleted="moveEntity(entity)" @archived="moveEntity(entity)" @published="moveEntity(entity)" class="panel__entity-actions--editable"></panel--entity-actions>
                </div>
            </div>
        </div>
    </template> -->

</panel--entity-tabs>