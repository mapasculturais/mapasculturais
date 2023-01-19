<?php

use MapasCulturais\i;

$this->import('modal loading panel--entity-actions select-entity mapas-card');
?>

<mapas-card>
    <div>
        <p><?= i::__('A remoção da conta fará com que a maioria de suas informações não sejas mas acessíveis publicamente.') ?></p>
        <p><?= i::__('Algumas informações, como por exemplo as inscrições em editais continuarão acessíveis. Você pode escolher por transferir suas entidades para outro usuário, 
                                que será questionado se deseja recebê-las. No caso do usuário se negar a receber as entidades, estas serão excluidas.') ?></p>
        <p><?= i::__('Se desejar escolha o usuário para receber suas entidades:') ?></p>
        <select-entity type="agent" @select="switchUser($event)" openside="down-right">
            <template #button="{ toggle }">
                <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()">
                  <?php i::_e('Selecione o usuário') ?>
                </button>
            </template>
        </select-entity>
    </div>
    <div>
        <!-- <panel--entity-actions :entity="user"></panel--entity-actions> -->
    </div>
</mapas-card>