<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<template v-if="isOpen">
    <div class="mc-side-menu">
        <div class="mc-side-menu--container">
            <div class="mc-side-menu--container_content">
                <slot>
                    Conteudo
                </slot>
            </div>
        </div>
    </div>
</template>