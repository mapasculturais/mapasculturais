<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-icon
    select-entity
');
?>

<div :class="classes">
    <div>
        <h4><?php i::_e("Selecione abaixo os campos que devem ser bloqueados nos agentes e espaços que possuírem este selo") ?></h4>
    </div>

    <div class="form_block_field__container">
        <h3><?php i::_e("Agentes") ?></h3>
        <div class="grid-12">
            <div v-for="(item, index) in agents" class="col-3">
                <input type="checkbox" @input="val => saveValueAgentLockedFields(val.target._value)" :value="item.value" /> {{ item.label }}
            </div>
        </div>
    </div>

    <div class="form_block_field__container">
        <h3><?php i::_e("Espaços") ?></h3>
        <div class="grid-12">
            <div v-for="(item, index) in spaces" class="col-3">
                <input type="checkbox" @input="val => saveValueSpaceLockedFields(val.target._value)" :value="item.value" /> {{ item.label }}
            </div>
        </div>
    </div>
</div>