<?php

use MapasCulturais\i;

$this->import('
    mc-icon  mc-map-card
');
?>
<div class="entity-seals__card">
    <div class="entity-seals__card--header">
        <label class="entity-seals__card--header-title">
            <slot name="title" />
        </label>
    </div>
    <div class="entity-seals__card--content ">
        <slot name="content" />
    </div>
</div>