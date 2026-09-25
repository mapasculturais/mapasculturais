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
    <?php $this->applyTemplateHook('country-address-form','begin'); ?>

    <div v-if="countryFieldEnabled" class="field col-12">
        <?php $this->applyTemplateHook('country-address-form-country-field','before'); ?>
        <label class="field__title">
            <?= i::__("País") ?>
            <span v-if="isCountryRequired" class="required">*<?php i::_e('obrigatório') ?></span>
        </label>
        <mc-select 
            :options="countries" 
            v-model:default-value="country" 
            @change-option="changeCountry" 
            placeholder="<?= i::__("País") ?>" 
            show-filter>
        </mc-select>
        <?php $this->applyTemplateHook('country-address-form-country-field','after'); ?>
    </div>

    <mc-loading :condition="processing" class="col-12"> <?= i::__('Carregando') ?></mc-loading>

    <div v-if="!processing && country" class="col-12 grid-12">
        <?php $this->applyTemplateHook('country-address-form','before'); ?>
        <brasil-address-form
            v-if="country == 'BR'"
            :entity="entity"
            :hierarchy="levelHierarchy"
            class="col-12"
            editable >
        </brasil-address-form>

        <?php $this->applyTemplateHook('country-address-form','forms'); ?>

        <international-address-form
            v-else
            :entity="entity"
            :country="country"
            :hierarchy="levelHierarchy"
            class="col-12"
            editable >
        </international-address-form>

        <?php $this->applyTemplateHook('country-address-form','after'); ?>
    </div>

    <?php $this->applyTemplateHook('country-address-form','end'); ?>
</div>