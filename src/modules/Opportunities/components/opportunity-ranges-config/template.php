<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-currency-input 
    mc-icon
');
?>
<div class="opportunity-ranges-config">
    <h4 class="bold"><?= i::__('Configuração por Faixas') ?></h4>
    <p><?= i::__('Crie e configure as faixas abaixo, inserindo um breve resumo, quantidade e valor de cada uma delas.') ?></p>

    <div v-for="(range, index) in entity.registrationRanges" :key="index">
        <h5 class="bold"><?= i::__('Faixa') ?> {{index+1}}</h5>

        <input type="text" v-model="range.label" @blur="autoSaveRange(range)" placeholder="<?= i::__('Descrição da faixa') ?>">
        <input type="number" v-model="range.limit" @blur="autoSaveRange(range)">
        <mc-currency-input v-model.lazy="range.value" @blur="autoSaveRange(range)"></mc-currency-input>
        <mc-confirm-button @confirm="removeRange(index)">
            <template #button="{open}">
                <mc-icon name="trash" @click="open()"></mc-icon>
            </template>
            <template #message="message">
                <?php i::_e('Deseja deletar a faixa?') ?>
            </template>
        </mc-confirm-button>
    </div>

    <button @click="addRange"><?= i::__('Adicionar Faixa') ?></button>
</div>