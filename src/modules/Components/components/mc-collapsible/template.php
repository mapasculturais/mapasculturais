<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('mc-icon');
?>

<div class="mc-collapsible">
    <header :class="['mc-collapsible__header', { 'mc-collapsible__header--open': isOpen }]" @click="toggle()">
        <div class="mc-collapsible__header-content">
            <slot name="header" :is-open="isOpen"></slot>
            <slot v-if="isOpen" name="header-open"></slot>
            <slot v-else name="header-closed"></slot>
        </div>
        <div class="mc-collapsible__header-icon">
            <mc-icon v-if="isOpen" name="arrowPoint-up"></mc-icon>
            <mc-icon v-else name="arrowPoint-down"></mc-icon>
        </div>
    </header>

    <div v-if="isOpen" class="mc-collapsible__body">
        <slot name="body"></slot>
    </div>

    <footer  class="mc-collapsible__footer">
        <slot name="footer" :is-open="isOpen"></slot>
        <slot v-if="isOpen" name="footer-open"></slot>
        <slot v-else name="footer-closed"></slot>
    </footer>
</div>