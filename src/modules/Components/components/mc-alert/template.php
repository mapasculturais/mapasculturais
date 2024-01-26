<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
')
?>

<div class="alert" :class="['alert', {'success': type=='success'}, {'helper': type=='helper'}, {'warning': type=='warning'}, {'danger': type=='danger'}]" v-if="showAlert">
    <div class="alert__message">
        <mc-icon v-if="type === 'helper'" name="info-full"></mc-icon>
        <mc-icon v-if="type === 'success'" name="circle-checked"></mc-icon>
        <mc-icon v-if="type === 'warning' || type === 'danger'" name="exclamation"></mc-icon>
        <p class="text">
            <slot></slot>
        </p>
    </div>
    <button v-if="closeButton" class="alert__button" @click="close"><?= i::__('Fechar') ?></button>
</div>