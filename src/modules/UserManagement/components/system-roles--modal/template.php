<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-loading
    mc-icon
    mc-modal
')
?>
<mc-modal :title="title" classes="create-modal system-roles-modal" @close="destroyInstance()" @open="createInstance()">
    <template v-if="instance" #default>
        <entity-field v-if="!entity" :entity="instance" prop="name" hide-required class="system-roles-modal__field"></entity-field>
        <form class="system-roles-modal__filter">
               <input type="text" class="system-roles-modal__input">
               <mc-icon name="search" class="system-roles-modal__icon"></mc-icon>
        </form>
        <h4 class="system-roles-modal__select"><?php i::_e("Selecione as permissões para essa função:") ?></h4>
        <section v-for="(entityPermissions,entitySlug) in permissions" :key="entitySlug" class="system-roles-modal__section">
            <h4 class="system-roles-modal__title">{{text(entitySlug)}}</h4>

            <ul class="system-roles-modal__list">
                <li v-for="permission in entityPermissions" class="system-roles-modal__item">
                    <label class="system-roles-modal__label">
                        <input type="checkbox" v-model="instance.permissions" :value="`${entitySlug}.${permission.permission}`">
                        {{permission.label || permission.permission}}
                    </label>
                    <i v-if="permission.description" class="hltip icon icon-help" :title="permission.description"></i>
                </li>
            </ul>
        </section>
    </template>

    <template #button="modal">
        <slot :modal="modal">
            <template v-if="entity">
                <button @click="modal.open()" class="button button--icon button button--primary">
                    <mc-icon name="edit"></mc-icon>
                    <?php i::_e("Editar") ?>
                </button>
            </template>
            <template v-else>
                <button @click="modal.open()" class="button button--icon button--primary">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e("Criar nova função de usuário") ?>
                </button>
            </template>
        </slot>
    </template>

    <template #actions="modal">
        <button class="button button--primary" @click="save(modal)">{{saveLabel}}</button>
        <button class="button button--text button--text-del" @click="modal.close()"><?php i::_e("Cancelar") ?></button>
    </template>
</mc-modal>