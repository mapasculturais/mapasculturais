<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
')
?>

<div class="tecnical-evaluation-form">
    <div>
        <h4><?php i::_e('Insira as notas nos campos abaixo') ?></h4>
    </div>
    <div class="tecnical-evaluation-form__fields" v-for="(section, sectionIndex) in sections" :key="section.name">
        <h3><strong>{{ section.name }}</strong></h3>
        <div class="field" v-for="criterion in section.criteria" :key="criterion.id">
            <label>{{ criterion.title }}</label>
            <input v-model="formData.data[criterion.id]" min="0" step="0.1" type="number" @input="handleInput(sectionIndex, criterion.id)">
        </div>
        <div>
            <label><strong>{{ subtotal (sectionIndex) }}</strong></label>
        </div>
    </div>

    <div class="tecnical-evaluation-form__textarea-adjusted">
        <p><strong><?php i::_e('Informe o Parecer técnico') ?></strong></p>
        <textarea v-model="formData.data.obs" rows="10" cols="36"></textarea>
    </div>
    
    <div class="tecnical-evaluation-form__viability-radio-group" v-if="enableViability">
        <h4><strong><?php i::_e('Exequibilidade orçamentária') ?></strong></h4>
        <p><?php i::_e('Esta proposta está adequada ao orçamento apresentado? Os custos orçamentários estão compatíveis com os praticados no mercado?') ?></p>
        <label>
            <input v-model="formData.data.viability" type="radio" name="confirmation" value="valid" /> <?php i::_e('Sim') ?>
        </label>
        <label>
            <input v-model="formData.data.viability" type="radio" name="confirmation" value="invalid" /> <?php i::_e('Não') ?>
        </label>
    </div>
    <div class="tecnical-evaluation-form__label-nolts-result">
        <div>
            <label><?php i::_e('Pontuação total:') ?> <strong>{{ notesResult }}</strong></label>
        </div>
        <div>
            <label><?php i::_e('Pontuação máxima:') ?> <strong>{{ totalMaxScore }}</strong></label>
        </div>
    </div>

    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>