<?php

use MapasCulturais\i;

$this->import('modal entities loading');
?>
 
 <modal title="<?php i::esc_attr_e('Adicionar função ao usuário') ?> " @open="createInstance()"> 
    <entities #default="{entities}" type="system-role" select="id,status,name,slug,permissions">
        <label><?= i::__('Função') ?></label>
        <select v-model="instance.permission">
            <option v-for="role in entities" v-bind:value="role.slug">{{role.name}}</option>
        </select>
    </entities>

    <entities #default="{entities}" type="subsite" select="id,status,name">
        <label><?= i::__('Subsite') ?></label>
        <select v-model="instance.subsite">
            <option v-for="subsite in entities" v-bind:value="subsite.id">{{subsite.name}}</option>
        </select>
    </entities>

    
    <template #actions="modal">
        <button class="button is-solid" @click="create(modal)"><?php i::_e("Confirmar") ?></button>
        <button class="button is-solid"><?php i::_e("Cancelar") ?></button>
    </template>

    <template #button="modal">
        <a @click="modal.open()">
        <?=i::__('Adicionar função')?>
            <iconify icon="mdi:plus" class="icon"></iconify>
        </a>
    </template>   
</modal>
