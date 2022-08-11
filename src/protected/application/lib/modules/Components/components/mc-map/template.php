<?php
use MapasCulturais\i;

$this->import('mc-map-marker');
?>
<div class="mc-map">
    <l-map 
        ref="map" 
        :zoom="defaultZoom" 
        :max-zoom="maxZoom" 
        :min-zoom="minZoom"
        :center="center" 
        zoom-animation 
        fade-animation 
        use-global-leaflet
        @ready="handleMapSetup()">
        <l-tile-layer :url="tileServer"></l-tile-layer>
        
        <slot></slot>
    </l-map>
</div>