<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-address-form-nacional
    entity-address-form-internacional
    mc-loading
    mc-select
');
?>

<div class="country-address-form grid-12">

    <template v-if="hasLinkedEntity">
        <div v-if="countryFieldEnabled" class="field col-12">
            <label><?= i::__("País") ?></label>
            <mc-select
                :options="countries"
                v-model:default-value="country"
                @change-option="changeCountry"
                placeholder="<?= i::__("País") ?>"
                show-filter>
            </mc-select>
            
            <mc-loading :condition="processing" class="col-12"> <?= i::__('Carregando') ?></mc-loading>
    
            <template v-if="!processing && country" class="col-12 grid-12">
    
                <entity-address-form-nacional
                    v-if="country == 'BR'"
                    :entity="entity[fieldName]"
                    :hierarchy="levelHierarchy"
                    has-public-location
                    class="col-12">
                </entity-address-form-nacional>

                <entity-address-form-internacional
                    v-else
                    :entity="entity[fieldName]"
                    :hierarchy="levelHierarchy"
                    :country="country"
                    has-public-location
                    class="col-12">
                </entity-address-form-internacional>
            </template>
        </div>
    </template>

    <template v-else>
        <small class="col-12 bold">
            {{feedback}}
        </small>
    </template>
</div>