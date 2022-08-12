<?php

use MapasCulturais\i;

$this->import('modal entities loading, entity');
?>
 
 <modal title="<?php i::esc_attr_e('Deletar role') ?>" @open="createInstance()">
    <template #default>
        <?php i::_e("Vocẽ tem certeza que quer deletar a permissão") ?>  <b>{{role.name}}</b>
    </template>
    
    <template #actions="modal">
        <button class="button is-solid" @click="deleteRole(modal)"><?php i::_e("Confirmar") ?></button>
        <button class="button is-solid" ><?php i::_e("Cancelar") ?></button>
    </template>
    
    <template #button="modal">
        <a @click="modal.open()">
            {{role.name}}
            <mc-icon name="delete"></mc-icon>
        </a>
    </template>

</modal>
