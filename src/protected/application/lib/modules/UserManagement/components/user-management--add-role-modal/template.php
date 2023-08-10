<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
    mc-entities 
    mc-modal 
');
?>
<mc-modal title="<?php i::esc_attr_e('Adicionar função ao usuário') ?>" @open="createInstance()"> 
    <template #default>
        <div class="field">
            <label><?= i::__('Função') ?></label>
            <select v-model="instance.name">
                <option :value="undefined"></option>
                <option value="saasSuperAdmin" ><?= i::__('Super Administrador da Rede') ?></option>
                <option value="saasAdmin" ><?= i::__('Administrador da Rede') ?></option>
                <option value="superAdmin" ><?= i::__('Super Administrador') ?></option>
                <option value="admin" ><?= i::__('Administrador') ?></option>
                <mc-entities #default="{entities}" type="system-role" select="id,status,name,slug,permissions" v-if="instance">
                    <option v-for="role in entities" v-bind:value="role.slug">{{role.name}}</option>
                </mc-entities>
            </select>            
            <template v-if="subsites?.length > 0" >
                <label><?= i::__('Subsite') ?></label>
                <select v-model="instance.subsiteId">
                    <option v-for="subsite in subsites" :value="subsite.id" >{{subsite.name}}</option>
                </select>
            </template>
        </div>
    </template>
    
    <template #actions="modal">
        <button class="button button--primary" @click="create(modal)"><?php i::_e('Confirmar')?></button>
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar')?></button>
    </template>

    <template #button="modal">
        <a @click="modal.open()" style="cursor: pointer;">
            <li class="primary__background mc-tag-list__tag mc-tag-list__tag--editable">    
                <?=i::__('Adicionar função')?>
                <mc-icon @click="modal.open()" name="add" is-link></mc-icon>
            </li>
        </a>
    </template>   
</mc-modal>
