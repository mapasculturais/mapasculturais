<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('
    mc-icon
')
?>

<div class="alert" :class="['alert', {'success': type=='success'}, {'helper': type=='helper'}, {'warning': type=='warning'}]" v-if="showAlert">
    <div class="alert__message">
        <mc-icon v-if="type !== 'success'" name="exclamation"></mc-icon>
        <mc-icon v-if="type === 'success'" name="circle-checked"></mc-icon>
        <p class="text">
            <?= i::__('Você tem inscrições não finalizadas. Acesse a aba')?> <strong><?= i::__('Não Enviadas') ?></strong> <?= i::__('para visualizar.') ?>
        </p>
    </div>
    <button v-if="closeButton" class="alert__button" @click="close"><?= i::__('Fechar') ?></button>
</div>