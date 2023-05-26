<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import("modal");
?>
<div class="error-display">
    <div class="error-display__content">
        <modal title="<?= i::__('Erro 403') ?>">
            <div class="content">
                    Teste

            </div>
            <template #actions="modal">
                <button class="button button--primary" @click="send(modal)"><?= i::__('Voltar para a página inicial') ?></button>
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('cancelar') ?></button>
            </template>


        </modal>
    </div>
</div>