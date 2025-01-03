<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-breadcrumb
    mc-title
');
?>
<div class="search">
    <mc-breadcrumb></mc-breadcrumb>

    <header class="search__header">
        <div class="search__header--content">
            <div class="search__header--content-left">
                <div :class="['search__header--content-left-icon', entityType+'__background']">
                    <mc-icon :name="entityType"></mc-icon>
                </div>
                
                <h1 class="mc-title mc-title--short bold"> {{pageTitle}} </h1>
            </div>

            <div class="search__header--content--right">
                <slot name="create-button"></slot>
            </div>
        </div>
    </header>
    
    <slot :pseudo-query="pseudoQuery" :changeTab="changeTab"></slot>
</div>