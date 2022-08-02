<?php
use MapasCulturais\i;

$this->import('mc-icon');
?>

<l-marker :lat-lng="entity.location" :draggable="draggable" @moveend="moved($event)">
    <l-icon>
        <div style="background-color: brown; padding: 5px; border-radius: 10px;">
            <mc-icon :entity="entity"></mc-icon>
        </div>
    </l-icon>
    <slot :entity="entity"></slot>
</l-marker>
