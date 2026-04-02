<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('mc-icon');
?>

<div class="oc-popover">
    <button class="button button--primary" @click.stop="togglePopover">
        <span><?= i::__('Enviar e-mail de teste') ?></span>
    </button>
    <transition name="fade">
        <div class="content" :class="`position-${position}`" v-show="toggle">
            <slot name="content" :popover="{toggle:togglePopover,_toggle:toggle}">
                <?= i::__('Conteúdo do popover') ?>
            </slot>
        </div>
    </transition>
</div>