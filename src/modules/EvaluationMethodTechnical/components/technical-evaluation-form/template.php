<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
')
?>

<div class="tecnical-evaluation-form">
    <div class="tecnical-evaluation-form__header">
        <h4 class="semibold"><?php i::_e('Insira as notas nos campos abaixo') ?></h4>
    </div>
    <div class="tecnical-evaluation-form__content" v-for="(section, sectionIndex) in sections" :key="section.name">
        <h3>{{ section.name }}</h3>
        <div class="bold tecnical-evaluation-form__criterion" v-for="criterion in section.criteria" :key="criterion.id">
            <label>{{ criterion.title }}</label>
            <div class="field tecnical-evaluation-form__maxScore">
                <label>Nota Máxima
                    <input class="maxScore-input" v-model="formData.data[criterion.id]" min="0" step="0.1" type="number" @input="handleInput(sectionIndex, criterion.id)">
                </label>
            </div>
        </div>
        <div class="tecnical-evaluation-form__content-subTotal">
            <h4 class="bold">Subtotal: {{ subtotal (sectionIndex) }}</h4>
        </div>
    </div>

    <div class="tecnical-evaluation-form__textarea">
        <h4 class="bold"><?php i::_e('Informe o Parecer técnico') ?></h4>
        <textarea v-model="formData.data.obs"></textarea>
    </div>

    <div class="tecnical-evaluation-form__viability-radio-group" v-if="enableViability">
        <h4 class="bold"><?php i::_e('Exequibilidade orçamentária') ?></h4>
        <p><?php i::_e('Esta proposta está adequada ao orçamento apresentado? Os custos orçamentários estão compatíveis com os praticados no mercado?') ?></p>
        <label>
            <input v-model="formData.data.viability" type="radio" name="confirmation" value="valid" /> <?php i::_e('Sim') ?>
        </label>
        <label>
            <input v-model="formData.data.viability" type="radio" name="confirmation" value="invalid" /> <?php i::_e('Não') ?>
        </label>
    </div>
    <div class="tecnical-evaluation-form__results">
        <div>
            <h4><?php i::_e('Pontuação total: ') ?><strong>{{ notesResult }}</strong></h4>
        </div>
        <div>
            <h4><?php i::_e('Pontuação máxima: ') ?><strong>{{ totalMaxScore }}</strong></h4>
        </div>
    </div>

    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>