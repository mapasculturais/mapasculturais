<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
')
?>
<div class="mc-map">
    <l-map
        ref="map"
        :zoom="defaultZoom"
        :max-zoom="maxZoom"
        :min-zoom="minZoom"
        :center="center"
        use-global-leaflet
        @ready="handleMapSetup()">
        <l-tile-layer :url="tileServer"></l-tile-layer>
        <slot></slot>

        <div ref="popup" style="display: none;">
            <slot v-if="popupEntity" name="popup" :entity="popupEntity"></slot>
        </div>
        <div ref="agent1" class="mc-map-marker is-agent">
            <mc-icon name="agent-1"></mc-icon>
        </div>
        <div ref="agent2" class="mc-map-marker is-agent">
            <mc-icon name="agent-2"></mc-icon>
        </div>
        <div ref="space" class="mc-map-marker is-space">
            <mc-icon name="space"></mc-icon>
        </div>
        <div ref="event" class="mc-map-marker is-event">
            <mc-icon name="event"></mc-icon>
        </div>
    </l-map>
</div>

