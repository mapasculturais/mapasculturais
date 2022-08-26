<?php
use MapasCulturais\i;
$this->import('popover');
?>
<div class="mc-header-user-menu">

    <!-- Menu desktop -->
    <popover openside="down-left">
        <template #button="{ toggle }">
            <slot name="button" :toggle="toggle"></slot>
        </template>
        <template #default="popover">
            <ul class="mc-header-user-menu__desktop">
                <slot name="default"></slot>
            </ul>
        </template>
    </popover>

    <!-- Menu mobile -->
    <div class="mc-header-user-menu__mobile">
        <div class="mc-header-user-menu__mobile--btn">
            <slot name="button" :toggle="toggleMobile"></slot>
        </div>    
        <ul v-if="open" class="mc-header-user-menu__mobile--list">
            <li class="close"> 
                <button class="close__btn" @click="toggleMobile()">
                    <mc-icon name="close"></mc-icon> 
                </button>
            </li>
            <slot name="default"></slot>
        </ul>
    </div>
    
</div>