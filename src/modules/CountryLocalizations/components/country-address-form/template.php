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

<div class="country-address-form">
  <div v-if="countryFieldEnabled">
    <label><?= i::__('País') ?>:</label>
    <select v-model="country" @change="changeCountry">
      <option v-for="c in countries" :key="c.sigla" :value="c.sigla">{{ c.nome_pais_int }}</option>
    </select>
  </div>

  <mc-loading :condition="processing" class="col-12"> <?= i::__('Carregando') ?></mc-loading>

  <div v-if="!processing && country">
    <brasil-address-form v-if="country == 'BR'" :entity="entity" :hierarchy="levelHierarchy" classes="col-12" editable></brasil-address-form>
    <international-address-form v-else :entity="entity" :country="country" :hierarchy="levelHierarchy" classes="col-12"></international-address-form>
  </div>
</div>