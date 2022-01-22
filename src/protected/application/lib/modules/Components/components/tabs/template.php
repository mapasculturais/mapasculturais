<?php
$this->import('tab');
?>
<div :class="wrapperClass">
    <ul role="tablist" :class="navClass">
        <li v-for="(tab, i) in tabs" 
            :key="i" 
            :class="[tab.navClass, navItemClass, tab.isDisabled ? navItemDisabledClass : '', tab.isActive ? navItemActiveClass : '' ]" 
            role="presentation">
            <a v-html="tab.header" 
                :aria-controls="tab.hash" 
                :aria-selected="tab.isActive" 
                @click="selectTab(tab.hash, $event)" 
                :href="tab.hash" 
                :class="[ navItemLinkClass, tab.isDisabled ? navItemLinkDisabledClass : '', tab.isActive ? navItemLinkActiveClass : '' ]" 
                role="tab"></a>
        </li>
    </ul>
    <div :class="panelsWrapperClass">
        <slot />
    </div>
</div>