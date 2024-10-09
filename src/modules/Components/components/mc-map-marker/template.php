<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
');
?>

<l-marker :lat-lng="entity.location" :draggable="draggable" @moveend="moved($event)">
    <l-icon>
        <div class="mc-map-marker" :class="'is-' + entity.__objectType">
            <mc-icon :entity="entity"></mc-icon>
        </div>
    </l-icon>
    <slot :entity="entity"></slot>
</l-marker>
