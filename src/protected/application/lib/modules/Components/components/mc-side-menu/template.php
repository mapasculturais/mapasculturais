<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>


<div class="mc-side-menu">
    <a href="#" class="mc-side-menu__button" @click="emitToggle">
        {{ textButton }}
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