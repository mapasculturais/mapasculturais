<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('mc-icon');
?>

<div class="oc-dialog" :class="{'active' : toggle}">
    <mc-icon name="one-click-dialog" @click="toggleDialog()"></mc-icon>
    <div class="triangle"></div>
    <transition name="fade">
        <div class="content" v-if="toggle">
            <slot name="content"></slot>
            <mc-icon name="one-click-close-rounded" class="close" @click="toggleDialog()"></mc-icon>
        </div>
    </transition>
</div>