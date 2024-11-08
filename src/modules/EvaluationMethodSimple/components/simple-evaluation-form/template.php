<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-select
    mc-modal
    evaluation-actions
    evaluation-simple-detail
');
?>
<mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
    <template #default>
        <evaluation-simple-detail :registration="entity"></evaluation-simple-detail>
    </template>

    <template #button="modal">
        <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir detalhamento') ?></button>
    </template>
</mc-modal>

<div class="simple-evaluation-form">
    <div class="simple-evaluation-form__form grid-12">
        <div class="simple-evaluation-form__header field col-12">
            <label class="field__label">
                <?php i::_e('Selecione o status dessa inscrição') ?>
            </label>
            <mc-select v-if="isEditable" :default-value="formData.status" @change-option="handleOptionChange" :options="statusList"></mc-select>
            <input v-if="!isEditable" type="text" :value="statusToString(formData.status)" disabled>
        </div>
        <div class="simple-evaluation-form__content field col-12">
            <label class="field__label">
                <?php i::_e('Insira suas Justificativas ou observações') ?>
            </label>
            <textarea v-if="isEditable" v-model="formData.obs"></textarea>
            <textarea v-if="!isEditable" disabled>{{formData.obs}}</textarea>
        </div>
    </div>
</div>