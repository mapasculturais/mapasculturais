<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="mc-select" @blur="console.log('oxe')">
    <div ref="selected" :id="uniqueID" :class="['mc-select__selected-option', {'mc-select__selected-option--open': open }]" @click="toggleSelect()"> 
    </div>

    <div v-show="open" class="mc-select__options" ref="options">
        <slot></slot>
    </div>
</div>