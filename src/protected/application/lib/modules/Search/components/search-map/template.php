<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-map 
    mc-map-card
    mc-loading
');
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
        @open-popup="openPopUp($event)">
        <template #popup="{entity}">
            <mc-map-card :entity="entity"></mc-map-card>
        </template>
    </mc-map>
    <mc-loading :condition="loading"></mc-loading>
</div> 