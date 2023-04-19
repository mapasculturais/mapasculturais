<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>


<div :class="['mc-side-menu', {'isOpen': isOpen}]">
    <button class="mc-side-menu__button" @click="toggleMenu()">
        <label class="label">{{textButton }}</label>
        <div class="icon">
            <mc-icon v-if="!isOpen" name="arrow-right-ios"></mc-icon>
            <mc-icon v-if="isOpen" name="arrow-left-ios"></mc-icon>
        </div>
    </button>


    <template v-if="isOpen">
        <div class="mc-side-menu__container" @click="emitToggle">
            <div class="mc-side-menu__container--content" @click="e => stopPropagation(e)">
                <slot>
                    {{ content }}
                </slot>
            </div>
        </div>
    </template>
</div>