<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div v-if="text" class="entity-description-collapse">
    <h3 v-if="label" class="entity-description-collapse__title bold">{{ label }}</h3>
    <div class="entity-description-collapse__body">
        <p class="entity-description-collapse__text" v-html="displayHtml"></p>
        <button
            v-if="needsToggle"
            type="button"
            class="entity-description-collapse__toggle"
            @click="expanded = !expanded"
        >
            {{ expanded ? '<?= i::__('(ver menos)') ?>' : '<?= i::__('(ver mais)') ?>' }}
        </button>
    </div>
</div>
