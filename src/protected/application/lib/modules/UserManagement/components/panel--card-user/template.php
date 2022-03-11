<?php

use MapasCulturais\i;

$this->import('
    panel--entity-card 
    user-management--add-role-modal
');
?>

<panel--entity-card :entity="entity">
    <template #title>
        <slot name="card-title" :entity="entity" v-if="entity.profile">{{entity.profile.name}}</slot>
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
                <li v-for="role in entity.roles" class="roles">
                    {{role.name}}
                    <iconify icon="mdi:close" class="icon"></iconify>
                </li>
                <li class="roles">    
                    <user-management--add-role-modal :user="entity"></user-management--add-role-modal>                
                </li>
            </ul>
        </slot>
    </template>
</panel--entity-card>