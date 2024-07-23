<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
')
?>
<div>
    <p>Critérios de Avaliação</p>
    <div v-for="section in sections" :key="section.id">
        <h3>{{ section.name }}</h3>
        <div v-for="crit in section.criteria" :key="crit.id">
            <label>{{ crit.name }}</label>
            <div>  
                <select v-model="formData.data[crit.id]" @change="handleChange(section.id)">
                    <option value=""><?php i::_e('Selecione') ?></option>
                    <option v-if="crit.notApplyOption == 'true'" value="Não se aplica"><?php i::_e('Não se aplica') ?></option>
                    <option value="Habilitado"><?php i::_e('Habilitado') ?></option>
                    <option value="Inabilitado"><?php i::_e('Inabilitado') ?></option>
                    <option v-for="option in crit.options" :key="option" :value="option">{{ option }}</option>
                </select>
            </div>
        </div>
        <label>Resultado da seção: {{ formData.sectionStatus[section.id] || section.status }}</label>
    </div>
    <div>
        <p>Observações</p>
        <textarea v-model="formData.data.obs"></textarea>
    </div>
    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>