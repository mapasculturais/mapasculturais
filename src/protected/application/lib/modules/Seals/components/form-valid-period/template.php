<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    select-entity
');
?>

<div>
  <mapas-card>
      <template #content>
          <label><input v-model="requiredPeriod" type="checkbox"> <?php i::_e('Este selo possui período de validade?') ?></label>
          <entity-field v-if="requiredPeriod" :entity="entity" hide-required prop="validPeriod" :min=1 label="<?php i::esc_attr_e("Numero de meses em que o selo é válido") ?>"></entity-field>
      </template>
  </mapas-card>
</div>
