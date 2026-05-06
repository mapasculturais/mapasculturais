<?php

use MapasCulturais\i;

$this->import('
    entity-field
    mc-modal
    mc-icon
');
?>
<mc-modal :title="modalTitle" classes="create-modal system-roles-modal" button-label="<?php i::_e('Criar Token')?>" @open="createEntity()" @close="destroyEntity()">
    <template v-if="entity && !entity.id" #default>
        <div class="create-modal__fields">
            <entity-field :entity="entity" label="<?php i::esc_attr_e('Nome do token')?>" prop="name"></entity-field>
            <div class="field">
                <label><?php i::_e('Data de expiração (opcional)') ?></label>
                <input type="date" v-model="entity.expiresAt" class="field__input" />
            </div>
            <div class="field">
                <label><?php i::_e('Permissões') ?></label>
                <small><?php i::_e('Selecione as permissões que este token terá.') ?></small>
                <section v-for="(entityPermissions, entitySlug) in permissionsList" :key="entitySlug" class="system-roles-modal__section">
                    <h4 class="system-roles-modal__title">{{ entitySlug }}</h4>
                    <ul class="system-roles-modal__list">
                        <li v-for="perm in entityPermissions" :key="perm.permission" class="system-roles-modal__item">
                            <label class="system-roles-modal__label">
                                <input type="checkbox" :value="entitySlug + '.' + perm.permission" v-model="entity.permissions" />
                                {{ perm.label || perm.permission }}
                            </label>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </template>

    <template v-if="entity && entity.id && plainTextToken" #default>
        <div class="pat-success">
            <div class="alert info">
                <mc-icon name="info"></mc-icon>
                <?php i::_e('Copie o token agora. Ele não será exibido novamente.') ?>
            </div>
            <div class="pat-token-display">
                <code class="pat-token-value">{{ plainTextToken }}</code>
                <button @click="copyToken" class="button button--primary button--icon">
                    <mc-icon :name="copied ? 'check' : 'copy'"></mc-icon>
                </button>
            </div>
        </div>
    </template>

    <template #button="modal">
        <slot :modal="modal">
            <button @click="modal.open()" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon> <?php i::_e('Criar Token') ?>
            </button>
        </slot>
    </template>

    <template v-if="entity && !entity.id" #actions="modal">
        <button class="button button--primary" @click="save(modal)"><?php i::_e('Criar') ?></button>
        <button class="button button--text button--text-del" @click="modal.close()"><?php i::_e('Cancelar') ?></button>
    </template>

    <template v-if="entity && entity.id" #actions="modal">
        <button @click="modal.close()" class="button button--primary-outline"><?php i::_e('Fechar') ?></button>
    </template>
</mc-modal>
