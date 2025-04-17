<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field-seals
    mc-tag-list
');
?>
<div class="entity-data">
    <div class="entity-data__label">{{propertyLabel}}</div>

    <div class="entity-data__data" v-if="propertyData">
        <!-- #region entity-data__value -->
        <div v-if="propertyType == 'date'" class="entity-data__value">
            {{propertyData.date('2-digit year')}}
        </div>
        
        <div v-else-if="propertyType == 'datetime'" class="entity-data__value">
            {{propertyData.date('2-digit year')}} <?= i::__('às') ?> {{propertyData.time('numeric')}}
        </div>
        
        <div v-else-if="propertyType == 'multiselect'">
            <mc-tag-list classes="space__background" :tags="propertyData"></mc-tag-list>
        </div>
        
        <div v-else-if="propertyType == 'radio' || propertyType == 'select'" class="entity-data__value">
            {{description.options[propertyData] || propertyData}}
        </div>
        
        <div v-else class="entity-data__value">
            {{propertyData}}
        </div>
        <!-- #endregion entity-data__value -->

        <slot name="seals">
            <entity-field-seals class="entity-data__seals" :entity="entity" :prop="prop"></entity-field-seals>
        </slot>
    </div>

    <div v-else class="entity-data__data">
        <div class="entity-data__value">
            <small v-if="!propertyData" class="bold">
                <?= i::__('Não informado') ?>
            </small>
        </div>
    </div>
</div>
