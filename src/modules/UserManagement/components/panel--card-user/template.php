<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-link
    panel--entity-card 
    user-management--add-role-modal
');
?>
<panel--entity-card :entity="entity">
    <template #title>
        <?php $this->applyComponentHook('title', 'begin') ?>
        <slot v-if="entity.profile" name="card-title" :entity="entity">{{username}}</slot>
        <?php $this->applyComponentHook('title', 'end') ?>
    </template>
    <template #header-actions>
        <?php $this->applyComponentHook('actions', 'begin') ?>
        <slot name="card-actions" :entity="entity"></slot>
        <?php $this->applyComponentHook('actions', 'end') ?>
    </template>

    <template #subtitle>
        <?php $this->applyComponentHook('subtitle', 'begin') ?>
        <slot v-if="global.showIds[entity.__objectType]" name="subtitle" :entity="entity">
            <code>ID: {{entity.id}}</code>
        </slot>
        <?php $this->applyComponentHook('subtitle', 'end') ?>
    </template>

    <template #default>
        <?php $this->applyComponentHook('content', 'begin') ?>
        <slot :entity="entity">
            <div class="mc-tag-list">
                <h4><?=i::__('Funções do usuário:')?></h4>
                <ul class="mc-tag-list__tagList">
                    <li v-for="role in roles" class="primary__border--solid primary__color mc-tag-list__tag mc-tag-list__tag--editable">
                        <strong v-if="role.subsite">{{`<?= i::esc_attr__('${role.name} em ${role.subsite.name}') ?>`}}</strong>
                        <strong v-else>{{role.name}}</strong>

                        <mc-confirm-button v-if="global.auth.is('superAdmin')" 
                            :message="`<?= i::esc_attr__('Deseja remover a função "${role.name}" do usuário "${username}"?') ?>`"
                            @confirm="deleteRole(role)">
                            <template #button="modal">
                                <mc-icon @click="modal.open()" name='delete'></mc-icon>
                            </template>

                        </mc-confirm-button>
                    </li>
                    <user-management--add-role-modal v-if="global.auth.is('superAdmin')" :user="entity"></user-management--add-role-modal>
                </ul>
            </div>
        </slot>
        <?php $this->applyComponentHook('content', 'end') ?>
    </template>

    <template #entity-actions-right>
        <?php $this->applyComponentHook('actions-right', 'begin') ?>
        <slot name="entity-actions-right" :entity="entity">
            <mc-link route="panel/user-detail" :params="[entity.id]" class="button button--primary-outline button--icon" icon="arrow-right" right-icon>
                <?= i::__('Acessar') ?>
            </mc-link>
        </slot>
        <?php $this->applyComponentHook('actions-right', 'end') ?>
    </template>
</panel--entity-card>