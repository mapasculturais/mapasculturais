<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<draggable tag="ul" :modelValue="list" @update:modelValue="reorderTabs" v-if="list">
    <slot></slot>
</draggable>
<ul v-else>
    <slot></slot>
</ul>
