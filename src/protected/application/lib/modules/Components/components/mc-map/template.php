<?php
use MapasCulturais\i;

$this->import('mc-map-marker');
?>
<div style="height: 400px;">
    <l-map v-model="defaultZoom" v-model:zoom="defaultZoom" :center="center">
        <l-tile-layer :url="tileServer"></l-tile-layer>
        <l-control-layers />
        <slot></slot>
    </l-map>
</div>