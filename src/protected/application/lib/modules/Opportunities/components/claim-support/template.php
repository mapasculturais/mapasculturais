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
<mc-modal :title="modalTitle" classes="claim-support" button-label="<?php i::_e('Discorda do resultado? Abra o formulário de recurso') ?>" button-classes="claim-support__buttonlabel" @close="modal.close()" @open="modal.open()">
    <template #default>
        <div class="claim-support__content">
            <h5 class="semibold claim-support__label"><?php i::_e('Descreva abaixo os motivos do recurso') ?></h5>
            <textarea v-model="claim.message" id="message" class="claim-support__textarea"></textarea>
        </div>
    </template>
    <template #actions="modal">
        <button class="button button--text delete-registration " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
        <button class="button button--primary"><?php i::_e('Solicitar') ?></button>
    </template>
</mc-modal>