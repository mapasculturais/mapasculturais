<?php

use MapasCulturais\i;

$this->import('
    tabs panel--entity-tabs,
    entities
')
?>

<slot name="card-user-management" :entity="entities">
    <panel--entity-tabs type="user" user="" select="id,email,status,profile.{id,name}">
        <template #card-title="{entity}">{{entity.profile.name}}</template>
        <template #card-content="{entity}">
            <dl>
                <dt><?=i::__('Funções')?></dt>
                <dd></dd>
            </dl>
        </template>
    </panel--entity-tabs>
</slot>