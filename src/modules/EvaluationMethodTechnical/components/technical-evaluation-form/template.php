<?php

use MapasCulturais\i;
?>

<div>
    <div>
        <h4><?php i::_e('Insira as notas nos campos abaixo') ?></h4>
    </div>
    <div v-for="(section, sectionIndex) in sections" :key="section.name">
        <h3>{{ section.name }}</h3>
        <div v-for="criterion in section.criteria" :key="criterion.id">
            <label>{{ criterion.title }}
                <input v-model.number="formData.data[criterion.id]" min="0" type="number" @input="handleInput(sectionIndex, criterion.id)">
            </label>
        </div>
        <div>
            <label>SubTotal: {{ subtotal(sectionIndex) }}</label>
        </div>
    </div>
    <hr>
    <div>
        <h3><?php i::_e('Informe o Parecer técnico') ?></h3>
        <textarea v-model="formData.data.obs" rows="10" cols="30"></textarea>
    </div>
    <h4><strong><?php i::_e('Exequibilidade orçamentária') ?></strong></h4>
    <p><?php i::_e('Esta proposta esta adequada ao orçamento apresentado? Os custos orçametários estão compatíveis com os praticados no mercado?') ?></p>
    <div>
        <input v-model="formData.data.viability" type="radio" name="confirmation" value="valid" /> <?php i::_e('Sim') ?>
        <input v-model="formData.data.viability" type="radio" name="confirmation" value="invalid" /> <?php i::_e('Não') ?>
    </div>
    <div>
        <div>
            <label><?php i::_e('Pontuação total:') ?> {{notesResult}}</label>

        </div>
        <div>
            <label><?php i::_e('Pontuação máxima:') ?> {{totalMaxScore}}</label>
        </div>
        <button @click="saveEvaluation()">Enviar</button>
    </div>
</div>