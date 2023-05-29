<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
    mc-map-card
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