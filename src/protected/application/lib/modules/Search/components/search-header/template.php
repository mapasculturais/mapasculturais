<?php
use MapasCulturais\i;
?>

<div class="mc-icon search-header">

    <div class="search-header__content">
        <div class="search-header__content--left">
            <div :class="['search-header__content--left-icon', type+'__background']">
                <mc-icon :name="type"></mc-icon>
            </div>
            
            <label class="search-header__content--left-label">{{entityName}}</label>
        </div>

        <div class="search-header__content--right">
            <slot name="create"></slot>
        </div>
    </div>
        
    <div class="search-header__actions">
        <slot name="actions">
        </slot>
    </div>

</div>
