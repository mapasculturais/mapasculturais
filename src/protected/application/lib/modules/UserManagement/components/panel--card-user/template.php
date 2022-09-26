<?php

use MapasCulturais\i;

$this->import('
    confirm-button
    panel--entity-card 
    user-management--add-role-modal
');
?>

<panel--entity-card :entity="entity">
    <template #title>
        <slot name="card-title" :entity="entity" v-if="entity.profile">{{username}}</slot>
    </template>
    <template #header-actions>
        <slot name="card-actions"></slot>
    </template>

    <template #subtitle>
        <code>ID: {{entity.id}}</code>
    </template>

    <template #default>
        <div class="mc-tag-list">
            <h4><?=i::__('Funções do usuário:')?></h4>
            <ul class="mc-tag-list__tagList">
                <li v-for="role in entity.roles" class="primary__border-solid primary__color mc-tag-list__tagList--tag">
                    <strong v-if="role.subsite">{{`<?= i::esc_attr__('${role.name} em ${role.subsite.name}') ?>`}}</strong>
                    <strong v-else>{{role.name}}</strong>
                    <confirm-button 
                        :message="`<?= i::esc_attr__('Deseja remover a função "${role.name}" do usuário "${username}"?') ?>`"
                        @confirm="role.delete(true)">
                        <template #button="modal">
                            <mc-icon @click="modal.open()" name='delete'></mc-icon>
                        </template>
                        
                    </confirm-button>
                </li>
                <li class="primary__background mc-tag-list__tagList--tag">    
                    <user-management--add-role-modal :user="entity"></user-management--add-role-modal>                
                </li>
            </ul>
        </div>
    </template>
</panel--entity-card>