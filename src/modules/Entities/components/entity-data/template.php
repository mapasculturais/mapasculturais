<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tag-list
');
?>
<div class="entity-data">
    <label class="entity-data__label">{{propertyLabel}}</label>

    <template v-if="propertyData">        
        <div v-if="propertyType == 'date'" class="entity-data__data">
            {{propertyData.date('2-digit year')}}
        </div>
        
        <div v-else-if="propertyType == 'datetime'" class="entity-data__data">
            {{propertyData.date('2-digit year')}} <?= i::__('às') ?> {{propertyData.time('numeric')}}
        </div>
        
        <div v-else-if="propertyType == 'multiselect'" :class="{'entity-data__data' : !propertyData}">
            <mc-tag-list classes="space__background" :tags="propertyData"></mc-tag-list>
        </div>
        
        <div v-else-if="propertyType == 'radio' || propertyType == 'select'" class="entity-data__data">
            {{description.options[propertyData]}}
        </div>
        
        <div v-else class="entity-data__data">
            {{propertyData}}
        </div>
    </template>


    <div v-if="!propertyData" class="entity-data__data">
        <small v-if="!propertyData" class="bold">
            <?= i::__('Não informado') ?>
        </small>
    </div>
</div>