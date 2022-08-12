<?php
use MapasCulturais\i;
$this->import('mc-map-markercluster mc-map');
?>
<div class="search-map">
    <mc-map>
        <mc-map-markercluster v-for="entity in entities" :key="entity.__objectId" :entity="entity"></mc-map-markercluster>
    </mc-map>
</div>