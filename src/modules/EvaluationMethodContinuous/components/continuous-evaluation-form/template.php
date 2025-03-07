<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    entity-file
    evaluation-actions
    evaluation-continuous-detail
    mc-modal
    mc-select
    registration-results
');
?>

<div class="continuous-evaluation-form">
    <h2 v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" class="needs-tiebreaker danger__background"><?= i::_e('Voto de minerva') ?></h2>
    <mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
        <template #default>
            <evaluation-continuous-detail :registration="entity"></evaluation-continuous-detail>
        </template>
    
        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Ver pareceres dos demais avaliadores') ?></button>
        </template>
    </mc-modal>
    <div class="continuous-evaluation-form__form grid-12">
        <div class="continuous-evaluation-form__header field col-12">

            <label class="field__label">
                <?php i::_e('Selecione o status dessa inscrição') ?>
            </label>
            <mc-select v-if="isEditable" :default-value="formData.data.status" @change-option="handleOptionChange" :options="statusList"></mc-select>
            <input v-if="!isEditable" type="text" :value="statusToString(formData.data.status)" disabled>
        </div>
        <div class="continuous-evaluation-form__content field col-12">
            <label class="field__label">
                <?php i::_e('Insira suas Justificativas ou observações') ?>
            </label>
            <textarea v-if="isEditable" v-model="formData.data.obs"></textarea>
            <textarea v-if="!isEditable" disabled>{{formData.data.obs}}</textarea>
        </div>
        <div class="continuous-evaluation-form__content field col-12">
            <label class="field__label" v-if="hasChatThread">
                <?php i::_e('Status') ?>
            </label>
            <div class="evaluation-form__status" v-if="isAwaitingMessage">
                <mc-icon name="clock"></mc-icon>
                <?php i::_e('Aguardando resposta do agente'); ?>
            </div>
            <div class="evaluation-form__status" v-else>
                <mc-icon name="exclamation" class="warning__color"></mc-icon>
                <span><?php i::_e('Aguardando validação') ?></span>
            </div>
        </div>

        <entity-file
            :entity="currentEvaluation"
            group-name="evaluationAttachment"
            title-modal="<?php i::_e('Anexar parecer') ?>"
            classes="col-12"
            title="<?php i::_e('Anexar parecer') ?>"
            :editable="currentEvaluation.status === 0"
            @delete="removeEvaluationAttachment"
            ></entity-file>

        <mc-modal v-if="!isEditable":title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
            <template #default>
                <evaluation-continuous-detail :registration="entity"></evaluation-continuous-detail>
            </template>
            
            <template #button="modal">
                <button class="button button--primary button--sm button--large col-12" @click="modal.open()"><?php i::_e('Ver detalhamento') ?></button>
            </template>
        </mc-modal>
    </div>
</div>