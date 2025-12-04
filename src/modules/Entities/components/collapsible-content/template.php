<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('mc-icon');
?>

<div :class="[classes, 'collapsible-content']">
    <header v-if="hasSlot('header')"
        :class="['collapsible-content__header', { 'collapsible-content__header--open': isOpen }]"
        @click="toggle">
        <div class="collapsible-content__header-content"><slot name="header"></slot></div>
        <div class="collapsible-content__header-icon">
            <mc-icon :name="iconName"></mc-icon>
        </div>
    </header>

    <div v-show="isOpen" class="collapsible-content__body">
        <slot name="body"></slot>
    </div>

    <footer v-show="isOpen && hasSlot('footer')" class="collapsible-content__footer">
        <slot name="footer"></slot>
    </footer>
</div>