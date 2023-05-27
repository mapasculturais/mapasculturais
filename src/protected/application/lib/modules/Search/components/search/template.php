<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mapas-breadcrumb
');
?>
<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>

    <header class="search__header">
        <div class="search__header--content">
            <div class="search__header--content-left">
                <div :class="['search__header--content-left-icon', entityType+'__background']">
                    <mc-icon :name="entityType"></mc-icon>
                </div>
                
                <label class="search__header--content-left-label"> {{pageTitle}} </label>
            </div>

            <div class="search__header--content--right">
                <slot name="create-button"></slot>
            </div>
        </div>
    </header>
    
    <slot :pseudo-query="pseudoQuery" :changeTab="changeTab"></slot>
</div>