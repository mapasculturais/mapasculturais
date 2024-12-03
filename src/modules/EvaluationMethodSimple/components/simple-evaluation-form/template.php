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

<div class="simple-evaluation-form">
    <h2 v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" class="needs-tiebreaker warning__background"><?= i::_e('Voto de minerva') ?></h2>
    <mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
        <template #default>
            <evaluation-simple-detail :registration="entity"></evaluation-simple-detail>
        </template>
    
        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Ver pareceres dos demais avaliadores') ?></button>
        </template>
    </mc-modal>
    <div class="simple-evaluation-form__form grid-12">
        <div class="simple-evaluation-form__header field col-12">
            <label class="field__label">
                <?php i::_e('Selecione o status dessa inscrição') ?>
            </label>
            <mc-select v-if="isEditable" :default-value="formData.data.status" @change-option="handleOptionChange" :options="statusList"></mc-select>
            <input v-if="!isEditable" type="text" :value="statusToString(formData.data.status)" disabled>
        </div>
        <div class="simple-evaluation-form__content field col-12">
            <label class="field__label">
                <?php i::_e('Insira suas Justificativas ou observações') ?>
            </label>
            <textarea v-if="isEditable" v-model="formData.data.obs"></textarea>
            <textarea v-if="!isEditable" disabled>{{formData.data.obs}}</textarea>
        </div>
    </div>
</div>