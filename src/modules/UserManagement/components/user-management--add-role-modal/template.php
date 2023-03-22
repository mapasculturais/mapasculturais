<?php

use MapasCulturais\i;

$this->import('modal entities loading');
?>
 
 <modal title="<?php i::esc_attr_e('Adicionar função ao usuário') ?> " @open="createInstance()"> 
    <template #default>
        <div class="field">
            <entities #default="{entities}" type="system-role" select="id,status,name,slug,permissions" v-if="instance">
                <label><?= i::__('Função') ?></label>
                    <select v-model="instance.name">
                    <option value="saasSuperAdmin" ><?= i::__('Super Administrador da Rede') ?></option>
                    <option value="saasAdmin" ><?= i::__('Administrador da Rede') ?></option>
                    <option value="superAdmin" ><?= i::__('Super Administrador') ?></option>
                    <option value="admin" ><?= i::__('Administrador') ?></option>
                    <option v-for="role in entities" v-bind:value="role.slug">{{role.name}}</option>
                </select>
            </entities>
            <label><?= i::__('Subsite') ?></label>
            <select v-model="instance.subsiteId">
                <option v-for="subsite in subsites" :value="subsite.id" >{{subsite.name}}</option>
            </select>
        </div>
    </template>
    
    <template #actions="modal">
        <button class="button button--primary" @click="create(modal)"><?php i::_e('Confirmar')?></button>
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar')?></button>
    </template>

    <template #button="modal">
        <a @click="modal.open()" style="cursor: pointer;">
            <?=i::__('Adicionar função')?>
            <mc-icon name="add"></mc-icon>
        </a>
    </template>   
</modal>
