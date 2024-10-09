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
                <div class="search__header--content-left-icon" :class="'is-' + entityType">
                    <mc-icon :name="entityType"></mc-icon>
                </div>

                <mc-title tag="h1" class="bold"> {{pageTitle}} </mc-title>
            </div>

            <div class="search__header--content--right">
                <slot name="create-button"></slot>
            </div>
        </div>
    </header>

    <slot :pseudo-query="pseudoQuery" :changeTab="changeTab"></slot>
</div>