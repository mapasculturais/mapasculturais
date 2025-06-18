<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    brasil-address-form
    international-address-form
    mc-loading
    mc-select
');
?>

<div class="country-address-form grid-12">
    <div v-if="countryFieldEnabled" class="field col-12">
        <label><?= i::__("País") ?></label>
        <mc-select 
            :options="countries" 
            v-model:default-value="country" 
            @change-option="changeCountry" 
            placeholder="<?= i::__("País") ?>" 
            show-filter>
        </mc-select>
    </div>

    <mc-loading :condition="processing" class="col-12"> <?= i::__('Carregando') ?></mc-loading>

    <div v-if="!processing && country" class="col-12 grid-12">
        <brasil-address-form
            v-if="country == 'BR'"
            :entity="entity"
            :hierarchy="levelHierarchy"
            class="col-12"
            editable >
        </brasil-address-form>

        <international-address-form
            v-else
            :entity="entity"
            :country="country"
            :hierarchy="levelHierarchy"
            class="col-12"
            editable >
        </international-address-form>
    </div>
</div>