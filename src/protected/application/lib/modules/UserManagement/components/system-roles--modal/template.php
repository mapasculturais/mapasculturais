<?php

use MapasCulturais\i;

$this->import('
    entity-field
    loading
    mc-icon
    modal
')
?>
<modal :title="title" classes="create-modal" @close="destroyInstance()" @open="createInstance()">
    <template v-if="instance" #default>
        <h1 v-if="entity"><?= i::__('Nome:') ?> {{instance.name}}</h1>
        <entity-field v-else :entity="instance" prop="name" hide-required></entity-field>
        <section v-for="(entityPermissions,entitySlug) in permissions"> 
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
            <button @click="modal.open()" class="button button--icon button--solid">
                <template v-if="entity">
                    <mc-icon name="edit"></mc-icon>
                    <?php i::_e("Editar") ?>
                </template>
                <template v-else>
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e("Criar nova função de usuário") ?>
                </template>
            </button>
        </slot>
    </template>     

    <template #actions="modal">
        <button class="button button--primary" @click="save(modal)">{{saveLabel}}</button>
        <button class="button button--text button--text-del" @click="modal.close()"><?php i::_e("Cancelar") ?></button>
    </template>
</modal>