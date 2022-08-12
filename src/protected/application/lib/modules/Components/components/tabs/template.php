<?php
$this->import('tab');
?>
<div class="tabs-component">
    <div class="tabs-component__header">
        <div class="tabs-component__header--left">
            <slot name="before-tablist"></slot>
            <ul class="tabs-component__buttons" role="tablist">
                <li v-for="tab in tabs" :key="tab.slug"
                    class="tabs-component__button"
                    :class="[tab.slug, tab.disabled && 'tabs-component__button--disabled', isActive(tab) && 'tabs-component__button--active']"
                    role="presentation">
                    <a
                        :aria-controls="tab.hash"
                        :aria-selected="isActive(tab)"
                        :href="tab.hash"
                        role="tab"
                        @click="selectTab(tab.slug, $event)">
                        <slot name="header" :tab="tab">
                            <mc-icon v-if="tab.icon" :name="tab.icon"></mc-icon>
                            <span>{{ tab.label }}</span>
                        </slot>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tabs-component__header--right">
            <slot name="after-tablist"></slot>
        </div>
    </div>
    <div class="tabs-component__panels">
        <slot></slot>
    </div>
</div>