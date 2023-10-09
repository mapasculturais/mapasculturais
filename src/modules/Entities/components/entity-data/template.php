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

    <div v-if="propertyType == 'date'" class="entity-data__data">
        {{propertyData.date('2-digit year')}}
    </div>

    <div v-else-if="propertyType == 'datetime'" class="entity-data__data">
        {{propertyData.date('2-digit year')}} <?= i::__('às') ?> {{propertyData.time('numeric')}}
    </div>

    <div v-else-if="propertyType == 'multiselect'" :class="{'entity-data__data' : !propertyData}">
        <mc-tag-list v-if="propertyData && propertyData.length > 0" classes="space__background" :tags="propertyData"></mc-tag-list> 
        <small v-if="!propertyData">
            <?= i::__('Não informado') ?>
        </small>
    </div>

    <div v-else-if="propertyType == 'radio' || propertyType == 'select'" class="entity-data__data">
        {{description.options[propertyData]}}
    </div>

    <div v-else class="entity-data__data">
        {{propertyData}}
    </div>
</div>

<!-- 
    boolean    
    CNPJ
    CPF
    point
    select
    readonly
 -->