<?php

use MapasCulturais\i;
?>
<div class="mapas-card">
    <div class="mapas-card__title">
        <slot name="title"></slot>
    </div>
    <div class="mapas-card__content">
        <slot></slot>
        <slot name="content"></slot>
    </div>
</div>