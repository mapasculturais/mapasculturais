<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('evaluation-actions');
?>
<div class="documentary-evaluation-form grid-12 field">
    <label><?= i::__('Avaliador') ?>: {{userName}}</label>

    <div v-if="enableForm" id="evaluation-form" class="documentary-evaluation-form__content col-12">
        <div class="documentary-evaluation-form__title">
            <h3>{{ formData.data[fieldId]?.label || '' }}</h3>
        </div>
        <input type="hidden" v-model="formData.data[fieldId].label" @change="setEvaluationData(fieldId)" />
        <div class="documentary-evaluation-form__fields field">
            <label>
                <input type="radio" value="" v-model="formData.data[fieldId].evaluation" @change="setEvaluationData(fieldId, 'empty')" :disabled="!isEditable"/>
                <?= i::__('Não avaliar') ?>
            </label>
            <label>
                <input type="radio" value="valid" v-model="formData.data[fieldId].evaluation" @change="setEvaluationData(fieldId, 'valid')" :disabled="!isEditable"/>
                <?= i::__('Válida') ?>
            </label>
            <label>
                <input type="radio" value="invalid" v-model="formData.data[fieldId].evaluation" @change="setEvaluationData(fieldId, 'invalid')" :disabled="!isEditable"/>
                <?= i::__('Inválida') ?>
            </label>
        </div>
        <div class="documentary-evaluation-form__textarea field">
            <label>
                <?= i::__('Descumprimento do(s) item(s) do edital:') ?>
                <textarea v-model="formData.data[fieldId].obsItems" @change="setEvaluationData(fieldId)" :disabled="!isEditable"></textarea>
            </label>
        </div>
        <div class="documentary-evaluation-form__textarea field">
            <label>
                <?= i::__('Justificativa / Observações') ?>
                <textarea v-model="formData.data[fieldId].obs" @change="setEvaluationData(fieldId)" :disabled="!isEditable"></textarea>
            </label>
        </div>
    </div>

    <evaluation-actions class="col-12" :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>