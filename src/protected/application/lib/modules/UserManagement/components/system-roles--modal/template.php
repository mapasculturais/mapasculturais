<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    loading
    mc-icon
    mc-modal
')
?>
<mc-modal :title="title" classes="create-modal" @close="destroyInstance()" @open="createInstance()">
    <template v-if="instance" #default>
        <entity-field v-if="!entity" :entity="instance" prop="name" hide-required></entity-field>
        <section v-for="(entityPermissions,entitySlug) in permissions" :key="entitySlug"> 
            <h4>{{text(entitySlug)}}</h4>
            <ul>
                <li v-for="permission in entityPermissions" style="display:inline-block; margin: 0.2em 0.5em;">
                    <label>    
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
                <button @click="modal.open()" class="button button--icon button button--solid">
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