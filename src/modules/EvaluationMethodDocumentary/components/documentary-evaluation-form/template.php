<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('evaluation-actions');
?>
<div>
    <label><?= i::__('Avaliador') ?>: {{userName}}</label>

    <div v-if="enableForm" id="evaluation-form">
        <h3>{{ formData.data[fieldId]?.label || '' }}</h3>
        <input type="hidden" v-model="formData.data[fieldId].label" />
        <div>
            <label>
                <input type="radio" value="" v-model="formData.data[fieldId].evaluation" :disabled="!isEditable"/>
                <?= i::__('Não avaliar') ?>
            </label>
            <label>
                <input type="radio" value="valid" v-model="formData.data[fieldId].evaluation" :disabled="!isEditable"/>
                <?= i::__('Válida') ?>
            </label>
            <label>
                <input type="radio" value="invalid" v-model="formData.data[fieldId].evaluation" :disabled="!isEditable"/>
                <?= i::__('Inválida') ?>
            </label>
        </div>
    
        <label>
            <?= i::__('Descumprimento do(s) item(s) do edital:') ?>
            <textarea v-model="formData.data[fieldId].obsItems" :disabled="!isEditable"></textarea>
        </label>
    
        <label>
            <?= i::__('Justificativa / Observações') ?>
            <textarea v-model="formData.data[fieldId].obs" :disabled="!isEditable"></textarea>
        </label>
    </div>

    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>