<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-container
    mc-modal
');
?>
<div v-if="isActive()">
    <mc-modal :title="modalTitle" classes="opportunity-claim-form" button-label="<?php i::_e('Discorda do resultado? Abra o formulário de recurso') ?>" button-classes="opportunity-claim-form__buttonlabel">
        <template #default>
            <div class="opportunity-claim-form__content">
                <h5 class="semibold opportunity-claim-form__label"><?php i::_e('Descreva abaixo os motivos do recurso') ?></h5>
                <textarea v-model="claim.message" id="message" class="opportunity-claim-form__textarea"></textarea>
            </div>
        </template>
        <template #actions="modal">
            <button class="button button--text delete-registration " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
            <button class="button button--primary" @click="sendClain()"><?php i::_e('Solicitar') ?></button>
        </template>
    </mc-modal>
</div>
