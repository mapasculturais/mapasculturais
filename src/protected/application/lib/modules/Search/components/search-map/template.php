<?php
use MapasCulturais\i;
$this->import('mc-map loading');
?>

<div class="search-map">
    <div class="search-map__filter">
        <div class="search-map__filter--filter">

            <slot name="filter"></slot>

        </div>
    </div>

    <mc-map :entities="entities" @close-popup="closePopup($event)"></mc-map>
    <loading :condition="loading"></loading>
</div> 