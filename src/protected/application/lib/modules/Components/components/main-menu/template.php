<?php
$this->import('popover');
?>
<div class="main-menu">

    <popover openside="down-left" button-label="Menu" button-classes="openPopever main-menu__btn"> 
        <ul class="main-menu__desktop">
            <slot name="default"></slot>
        </ul>
    </popover>

    <!-- Menu mobile -->
    <a class="main-menu__mobile--btn">
        <iconify icon="icon-park-outline:hamburger-button" />
    </a>    
    <ul class="main-menu__mobile">
        <slot name="default"></slot>
    </ul>
    
</div>