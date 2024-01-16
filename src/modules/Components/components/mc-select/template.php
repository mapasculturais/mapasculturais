<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="mc-select" @blur="open = false">
    <div :class="['mc-select__selected-option', {'mc-select__selected-option--open': open }]" @click="toggleSelect()">
        {{ selected.text }}
    </div>

    <div v-show="open" class="mc-select__options" ref="options">
        <slot></slot>
    </div>
</div>