<?php
$this->import('tab');
?>
<div class="tabs-component__controls">
    <ul class="tabs-component__buttons" role="tablist">
        <li v-for="tab in tabs" :key="tab.slug"
            class="tabs-component__button"
            :class="[tab.slug, tab.disabled && 'tab-component__button--disabled', (tab.slug === activeTab.slug) && 'tab-component__button--active']"
            role="presentation">
            <a
                :aria-controls="tab.hash"
                :aria-selected="tab.slug === activeTab.slug"
                :href="tab.hash"
                role="tab"
                @click="selectTab(tab.slug, $event)">
                <slot name="header" :tab="tab">
                    <span>{{ tab.label }}</span>
                </slot>
            </a>
        </li>
    </ul>
    <div class="tabs-component__panels">
        <slot></slot>
    </div>
</div>