<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>


<div class="mc-side-menu">
    <a href="#" class="side-button" @click="emitToggle">
        <div class="side-button__content">
            <label class="side-button__content--text">{{textButton }}</label>
            <div class="side-button__content--icon">
                <mc-icon name="arrow-right-ios"></mc-icon>
            </div>
        </div>

    </a>
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