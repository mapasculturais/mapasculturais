<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('
    mc-card
    panel--entity-actions 
    select-entity 
');
?>

<mc-card>
    <div>
        <p><?= i::__('A remoção da conta fará com que a maioria de suas informações não sejas mas acessíveis publicamente.') ?></p>
        <p><?= i::__('Algumas informações, como por exemplo as inscrições em editais continuarão acessíveis. Você pode escolher por transferir suas entidades para outro usuário, 
                                que será questionado se deseja recebê-las. No caso do usuário se negar a receber as entidades, estas serão excluidas.') ?></p>
        <p><?= i::__('Se desejar escolha o usuário para receber suas entidades:') ?></p>
        <div class="user-management__content--action">
            <select-entity type="agent" @select="switchUser($event)">
                <template #button="{ toggle }">
                    <button class="button button--sm button--icon button--primary" @click="toggle()">
                    <?php i::_e('Selecione o usuário') ?>
                    </button>
                </template>
            </select-entity>
        </div>
    </div>
    <div>
        <!-- <panel--entity-actions :entity="user"></panel--entity-actions> -->
    </div>
</mc-card>