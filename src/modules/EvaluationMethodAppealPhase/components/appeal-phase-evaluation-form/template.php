<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    entity-file
    evaluation-actions
    evaluation-appeal-phase-detail
    mc-modal
    mc-select
    registration-results
');
?>
<div class="appeal-phase-evaluation-form">
    <div class="appeal-phase-evaluation-form__form grid-12">
        <div class="appeal-phase-evaluation-form__header field col-12">
            <label class="field__label">
                <?php i::_e('Selecione o status dessa inscrição') ?>
            </label>
            <mc-select v-if="isEditable" :default-value="formData.data.status" @change-option="handleOptionChange" :options="statusList"></mc-select>
            <input v-if="!isEditable" type="text" :value="statusToString(formData.data.status)" disabled>
        </div>
        <div class="appeal-phase-evaluation-form__content field col-12">
            <label class="field__label">
                <?php i::_e('Insira suas Justificativas ou observações') ?>
            </label>
            <textarea v-if="isEditable" v-model="formData.data.obs"></textarea>
            <textarea v-if="!isEditable" disabled>{{formData.data.obs}}</textarea>
        </div>

        <entity-file
            :entity="currentEvaluation"
            group-name="evaluationAttachment"
            title-modal="<?php i::_e('Anexar parecer') ?>"
            classes="col-12"
            title="<?php i::_e('Anexar parecer') ?>"
            editable
            ></entity-file>

        <mc-modal v-if="!isEditable":title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
            <template #default>
                <evaluation-appeal-phase-detail :registration="entity"></evaluation-appeal-phase-detail>
            </template>
            
            <template #button="modal">
                <button class="button button--primary button--sm button--large col-12" @click="modal.open()"><?php i::_e('Ver detalhamento') ?></button>
            </template>
        </mc-modal>
    </div>
</div>