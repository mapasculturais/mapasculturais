<?php
use MapasCulturais\i;

$this->import('mc-map-marker');
?>
<div class="mc-map">
    <l-map v-model="defaultZoom" v-model:zoom="defaultZoom" :center="center" zoom-animation fade-animation>
        <l-tile-layer :url="tileServer"></l-tile-layer>
        
        <slot></slot>
    </l-map>
</div>