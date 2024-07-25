<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    mc-select
')
?>
<div>
    <p><?php i::_e('Critérios de Avaliação') ?></p>
    <div v-for="section in sections" :key="section.id">
        <h3>{{ section.name }}</h3>
        <div v-for="crit in section.criteria" :key="crit.id">
            <label>{{ crit.name }}</label>
            <div>
                <mc-select v-if="isEditable" v-model="formData.data[crit.id]" @change-option="updateSectionStatus(section.id, crit.id, $event)" :disabled="!isEditable">
                    <option value=""><?php i::_e('Selecione') ?></option>
                    <option v-if="crit.notApplyOption == 'true'" value="Não se aplica"><?php i::_e('Não se aplica') ?></option>
                    <option value="Habilitado"><?php i::_e('Habilitado') ?></option>
                    <option value="Inabilitado"><?php i::_e('Inabilitado') ?></option>
                    <option v-for="option in crit.options" :key="option" :value="option">{{ option }}</option>
                </mc-select>
                <input v-if="!isEditable" type="text" :value="formData.data[crit.id]" disabled>
            </div>
        </div>
        <label><?php i::_e('Resultado da seção:') ?> {{sectionStatus(section.id)}} </label>
    </div>
    <div v-if="statusText">
        <label><?php i::_e('Resultado da seção:') ?> {{statusText}}</label>
    </div>
    <div>
        <p><?php i::_e('Observações') ?></p>
        <textarea v-model="formData.data.obs" :disabled="!isEditable"></textarea>
    </div>
    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>