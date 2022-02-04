<?php

use MapasCulturais\i;

$this->import('modal,loading,field')
?>
<modal title="<?php i::esc_attr_e('Criar nova função') ?> " @close="resetInstance()" @open="resetInstance()">
    <field :entity="instance" prop="slug"></field>
    <field :entity="instance" prop="name"></field>
    <section v-for="(entityPermissions,entitySlug) in permissions"> 
        <h4>{{entitySlug}}</h4>
        <ul>
            <li v-for="permission in entityPermissions" style="display:inline-block; margin: 0.2em 0.5em;">
                <input type="checkbox" :id="`new-system-role--${entitySlug}--${permission.permission}`" v-model="instance.permissions" :value="`${entitySlug}.${permission.permission}`">
                <label :for="`new-system-role--${entitySlug}--${permission.permission}`"> {{permission.label || permission.permission}}</label>
                <i v-if="permission.description" class="hltip icon icon-help" :title="permission.description"></i>
            </li> 
        </ul>
    </section>

    <template #button="modal">
        <button @click="modal.open()"><slot></slot></button>
    </template>     

    <template #actions="modal">
        <template v-if="!instance.__processing">
            <button @click="create(modal)"><?php i::_e("Criar") ?></button>
            <button @click="cancel(modal)"><?php i::_e("Cancelar") ?></button>
        </template>
        <loading :entity="instance"></loading>
    </template>
</modal>