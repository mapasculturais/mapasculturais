<?php
$this->import('popover');
?>
<div class="main-menu">

    <popover openside="down-left" button-label="Menu" :button-classes="['openPopever', 'main-menu__btn']"> 
        <ul class="main-menu__desktop">
            <slot name="default"></slot>
        </ul>
    </popover>

    <!-- Menu mobile -->
    <a class="main-menu__mobile--btn">
        <mc-icon name="menu-mobile"></mc-icon>
    </a>    
    <ul class="main-menu__mobile">
        <slot name="default"></slot>
    </ul>
    
</div>