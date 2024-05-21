<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-select
');
?>

<div class="simple-evaluation-form">
    <div class="simple-evaluation-form__form grid-12">
        <div class="simple-evaluation-form__header field col-12">
            <label class="mc-select-label">
                <?php i::_e('Selecione o status dessa inscrição') ?>
            </label>
            <mc-select :default-value="formData.status" :disabled="!editable" @change-option="handleOptionChange" :options="statusList"></mc-select>
        </div>
        <div class="simple-evaluation-form__content field col-12">
            <label class="textarea-label">
                <?php i::_e('Insira suas Justificativas ou observações') ?>
            </label>
            <textarea v-model="formData.obs"></textarea>
        </div>
    </div>
</div>