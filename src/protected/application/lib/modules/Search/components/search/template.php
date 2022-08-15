<?php
use MapasCulturais\i;
?>

<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>

    <div class="mc-icon search-header">
        <div class="search-header__content">
            <div class="search-header__content--left">
                <div :class="['search-header__content--left-icon', entityType+'__background']">
                    <mc-icon :name="entityType"></mc-icon>
                </div>
                
                <label class="search-header__content--left-label"> {{pageTitle}} </label>
            </div>

            <div class="search-header__content--right">
                <slot name="create-button"></slot>
            </div>
        </div>
            
        <div class="search-header__actions">
            <slot :query="query"></slot>
        </div>
    </div>
</div>