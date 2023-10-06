<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="entity-data">
    <label class="entity-data__label">{{propertyLabel}}</label>
    <div v-if="propertyType == 'date'" class="entity-data__data">
        {{propertyData.date('2-digit year')}}
    </div>
    <div v-else-if="propertyType == 'datetime'" class="entity-data__data">
        {{propertyData.date('2-digit year')}} <?= i::__('às') ?> {{propertyData.time('numeric')}}
    </div>
    <div v-else class="entity-data__data">
        {{propertyData}}
    </div>
</div>