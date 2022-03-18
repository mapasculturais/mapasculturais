<?php

use MapasCulturais\i;

$this->import('modal entities loading, entity');
?>
 
 <modal title="<?php i::esc_attr_e('Deletar role') ?>" @open="createInstance()">
   
    <?php i::_e("Vocẽ tem certeza que quer deletar a permissão") ?>  <b>{{role.name}}</b>
    
    <template #actions="modal">
        <button class="button is-solid" @click="deleteRole(modal)"><?php i::_e("Confirmar") ?></button>
        <button class="button is-solid" ><?php i::_e("Cancelar") ?></button>
    </template>
    
    <template #button="modal">
        <a @click="modal.open()">
            {{role.name}}
            <iconify icon="mdi:close" class="icon"></iconify>
        </a>
    </template>

</modal>
