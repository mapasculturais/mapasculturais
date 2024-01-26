<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-tab
');
?>
<div class="tabs-component" :class="classes">
    <div class="tabs-component__header">
        <div class="tabs-component__header--left">
            <slot name="before-tablist"></slot>
            <ul class="tabs-component__buttons" role="tablist">
                <li v-for="tab in tabs" :key="tab.slug"
                    class="tabs-component__button"
                    :class="[tab.slug, tab.class, tab.disabled && 'tabs-component__button--disabled', isActive(tab) && 'tabs-component__button--active']"
                    role="presentation">
                    <a
                        :aria-controls="tab.hash"
                        :aria-selected="isActive(tab)"
                        :href="tab.hash"
                        role="tab"
                        @click="selectTab(tab.slug, $event)">
                        <slot name="header" :tab="tab">
                            <mc-icon v-if="tab.icon && iconPosition=='left'" :name="tab.icon"></mc-icon>
                            <span>{{ tab.label }}</span>
                            <mc-icon v-if="tab.icon && iconPosition=='right'" :name="tab.icon"></mc-icon>
                        </slot>
                        
                    </a>
                </li>
            </ul>
        </div>

        <div class="tabs-component__header--right">
            <slot name="after-tablist"></slot>
        </div>
    </div>

    <div v-if="hasSlot('tabs-alert')" class="tabs-component__alert">
        <slot name="tabs-alert"></slot>
    </div>

    <div class="tabs-component__panels">
        <slot></slot>
    </div>
</div>