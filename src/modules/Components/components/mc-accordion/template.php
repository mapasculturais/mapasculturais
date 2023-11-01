<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tag-list
    mc-title
');
?>
<section  class="mc-accordion">
    <header @click="toggle()" class="mc-accordion__header">
        <mc-title tag="h3" class="bold mc-accordion__title">
            <slot name="title"></slot>
        </mc-title>
        <mc-icon :name="active ? 'arrowPoint-up' : 'arrowPoint-down'" class="primary__color"></mc-icon>
    </header>
    <div v-if="active" class="mc-accordion__content">
        <slot name="content"></slot>
    </div>
</section>