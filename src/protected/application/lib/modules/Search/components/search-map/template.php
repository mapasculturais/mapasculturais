<?php
use MapasCulturais\i;
$this->import('mc-map mc-map-card loading');
?>

<div class="search-map">
    <div class="search-map__filter">
        <div class="search-map__filter--filter">
            <slot name="filter"></slot>
        </div>
    </div>

    <mc-map :entities="entities" @close-popup="closePopup($event)">
        <template #popup="{entity}">
            <mc-map-card :entity="entity"></mc-map-card>
        </template>
    </mc-map>
    <loading :condition="loading"></loading>
</div> 