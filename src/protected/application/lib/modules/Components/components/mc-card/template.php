<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<component :is="tag" class="mc-card" :class="classes">
    <header v-if="hasSlot('title')" class="mc-card__title">
        <slot name="title"></slot>
    </header>
    <main class="mc-card__content">
        <slot></slot>
        <slot name="content"></slot>
    </main>
</component>