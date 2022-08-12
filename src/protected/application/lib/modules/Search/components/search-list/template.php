<?php
use MapasCulturais\i;
$this->import('mc-map-markercluster mc-map entities');
?>
<div class="search-list">
    <div class="grid-12">
        <div class="col-12" v-for="entity in entities" :key="entity.id">
            <entity-card :entity="entity"></entity-card> 
        </div>
    </div>
</div>