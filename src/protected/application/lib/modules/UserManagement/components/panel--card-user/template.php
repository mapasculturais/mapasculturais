<?php

use MapasCulturais\i;

$this->import('panel--entity-card');
?>

<panel--entity-card :entity="entity">
    <template #title>
        <slot name="card-title" :entity="entity">{{entity.profile.name}}</slot>
    </template>
    <template #header-actions>
        <slot name="card-actions">
            <button class="entity-card__header-action">
                <iconify icon="mdi:star-outline"></iconify>
                <span><?=i::__('Favoritar')?></span>
            </button>
        </slot>
    </template>
    <template #default>
        <slot name="card-content">
            <?=i::__('Funções')?>
            <ul>
                <li v-for="role in entity.roles" class="roles">{{role.name}}</li>
            </ul>
        </slot>
    </template>
</panel--entity-card>