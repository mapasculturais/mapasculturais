<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    brasil-address-form
    international-address-form
    mc-select
');
?>

<div class="country-address-form">
  <div v-if="countryFieldEnabled">
    <label><?= i::__('País') ?>:</label>
    <mc-select placeholder="<?= i::esc_attr__('Selecione um país') ?>" v-model:default-value="country" @change-option="changeCountry">
      <option v-for="c in countries" :key="c.sigla" :value="c.sigla">{{ c.nome_pais_int }}</option>
    </mc-select>
  </div>

  <brasil-address-form v-if="country == 'BR'" :entity="entity" classes="col-12" editable></brasil-address-form>
  <international-address-form v-else :entity="entity" :country="country" classes="col-12"></international-address-form>
</div>