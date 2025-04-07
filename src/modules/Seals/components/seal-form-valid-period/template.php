<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    entity-field
');
?>
<div class="field">
    <label> <?= i::__('Validade do certificado do selo') ?></label>
    <div class="field__group">
        <label class="input__label input__checkboxLabel input__multiselect">
            <input v-model="requiredPeriod" type="checkbox"> <?php i::_e('Habilitar selo com validade') ?>
        </label>
        <entity-field v-if="requiredPeriod" :entity="entity" hide-required prop="validPeriod" min="1" label="<?php i::_e("Insira o número de meses que o certificado do selo deve ser válido") ?>"></entity-field>
    </div>
</div>