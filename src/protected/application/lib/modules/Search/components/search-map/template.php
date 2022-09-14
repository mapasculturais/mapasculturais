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

    <mc-map 
        :entities="entities" 
        @ready="$emit('ready', $event)" 
        @close-popup="$emit('closePopup', $event)" 
        @open-popup="$emit('openPopup', $event)">
        <template #popup="{entity}">
            <mc-map-card :entity="entity"></mc-map-card>
        </template>
    </mc-map>
    <loading :condition="loading"></loading>
</div> 