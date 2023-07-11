<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal 
');
?>

<mc-modal :title="modalTitle" button-label="<?php i::_e('Importar Evento') ?>">
    <template #default>
        <p><?php i::_e('Para importar eventos, faça upload da planilha de modelo preechida') ?></p>

        <form>
            <input type="file" name="file" @change="setFile" ref="file">
        </form>

    </template>

    <template #actions="modal">
        <button><?php i::_e('Cancelar') ?></button>
        <button @click="upload(modal)"><?php i::_e('Enviar') ?></button>
    </template>
</mc-modal>

<a :href="csvUrl" class="button button--icon button--sm event__color"> <?= i::__('Baixar modelo CSV') ?></a>
<a :href="xlsUrl" class="button button--icon button--sm event__color"> <?= i::__('Baixar modelo XLS') ?></a>