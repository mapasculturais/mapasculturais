<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    evaluation-actions
    evaluation-documentary-datail
    mc-modal
    mc-avatar
');
?>

<div class="documentary-evaluation-form grid-12 field">
    <h2 v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" class="needs-tiebreaker danger__background"><?= i::_e('Voto de minerva') ?></h2>
    <mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
        <template #default>
            <evaluation-documentary-datail :registration="entity"></evaluation-documentary-datail>
        </template>
    
        
        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Ver pareceres dos demais avaliadores') ?></button>
        </template>
    </mc-modal>
    <label><?= i::__('Avaliador') ?>: {{userName}}</label>

    <div v-if="enableForm" id="evaluation-form" class="documentary-evaluation-form__content col-12">
        <div class="documentary-evaluation-form__title">
            <h3>{{ formData.data[fieldId]?.label || '' }}</h3>
        </div>
        <input type="hidden" v-model="formData.data[fieldId].label" @change="setEvaluationData(fieldId)" />

        <div v-if="lockedFields" class="field documentary-evaluation-form__verification">
            <div class="documentary-evaluation-form__verification__seals-image">
                <div v-for="seal in getSealInfo(fieldId)" :key="seal.name">
                    <mc-avatar :entity="seal" size="small" square></mc-avatar>
                </div>
            </div>

            <h4 class="bold"><?= i::__('Documento verificado') ?></h4>
            <p><?= i::__('Este documento foi verificado automaticamente via ') ?>
                <span v-html="formatSealsInfo(getSealInfo(fieldId))"></span>
            </p>
        </div>

        <div class="documentary-evaluation-form__fields field">
            <p><?= i::__('Selecione se o dado informado no campo é válido ou não:') ?></p>
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
</div>