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
        <div class="field col-12">
            <div v-if="countryFieldEnabled">
                <label>
                    <?= i::__("País") ?>
                    <span v-if="requiredAddressFields.address_level0" class="required">*</span>
                </label>
                <mc-select
                    :options="countries"
                    v-model:default-value="country"
                    @change-option="changeCountry"
                    placeholder="<?= i::__("País") ?>"
                    :has-public-location="hasPublicLocation"
                    show-filter>
                </mc-select>
            </div>
            
            <mc-loading :condition="processing" class="col-12"> <?= i::__('Carregando') ?></mc-loading>
    
            <template v-if="!processing && country" class="col-12 grid-12">
    
                <entity-address-form-nacional
                    v-if="country == 'BR'"
                    :entity="entity[fieldName]"
                    :hierarchy="levelHierarchy"
                    :has-public-location="hasPublicLocation"
                    :required-fields="requiredAddressFields"
                    :has-errors="hasLocationErrors"
                    :missing-keys="missingLocationKeys"
                    class="col-12">
                </entity-address-form-nacional>

                <entity-address-form-internacional
                    v-else
                    :entity="entity[fieldName]"
                    :hierarchy="levelHierarchy"
                    :country="country"
                    :required-fields="requiredAddressFields"
                    :has-errors="hasLocationErrors"
                    :missing-keys="missingLocationKeys"
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