<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('evaluation-actions');
?>

<div v-if="enableForm" id="evaluation-form">
    <h3>{{ formData.data[fieldId]?.label || '' }}</h3>
    <input type="hidden" v-model="formData.data[fieldId].label" />
    <div>
        <label>
            <!-- AJUSTAR VALUE DESTE INPUT -->
            <input type="radio" value="nao-avaliar" v-model="formData.data[fieldId].evaluation" />
            <?= i::__('Não avaliar') ?>
        </label>
        <label>
            <input type="radio" value="valid" v-model="formData.data[fieldId].evaluation" />
            <?= i::__('Válida') ?>
        </label>
        <label>
            <input type="radio" value="invalid" v-model="formData.data[fieldId].evaluation" />
            <?= i::__('Inválida') ?>
        </label>
    </div>

    <label>
        <?= i::__('Descumprimento do(s) item(s) do edital:') ?>
        <textarea v-model="formData.data[fieldId].obsItems"></textarea>
    </label>

    <label>
        <?= i::__('Justificativa / Observações') ?>
        <textarea v-model="formData.data[fieldId].obs"></textarea>
    </label>

    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>