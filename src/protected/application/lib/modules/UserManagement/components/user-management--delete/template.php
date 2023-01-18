<?php

use MapasCulturais\i;

$this->import('modal loading panel--entity-actions');
?>
 
 <modal title="<?php i::esc_attr_e('Exclua sua conta') ?> " @open="">
    <template #default>
        <div>
            <p><?= i::__('A remoção da conta fará com que a maioria de suas informações não sejas mas acessíveis publicamente.') ?></p>
            <p><?= i::__('Algumas informações, como por exemplo as inscrições em editais continuarão acessíveis. Você pode escolher por transferir suas entidades para outro usuário, 
                                que será questionado se deseja recebê-las. No caso do usuário se negar a receber as entidades, estas serão excluidas.') ?></p>
            <p><?= i::__('Se desejar escolha o usuário para receber suas entidades:') ?></p>
            <button class="button button--text">Selecione o usuário</button>
        </div>
    </template>
    
    <template #actions="modal">
        <button class="button button--text button--text-del" @click="modal.close()"><?php i::_e('Cancelar')?></button>
<!--        <panel--entity-actions :entity="user"></panel--entity-actions>-->
        <button class="button button--primary" @click="delete(modal)"><?php i::_e('Excluir conta')?></button>
    </template>

    <template #button="modal">
        <a @click="modal.open()" style="cursor: pointer; font-size: 14px;">
            <mc-icon name="trash"></mc-icon> <?=i::__('Excluir')?>
        </a>
    </template>   
</modal>
