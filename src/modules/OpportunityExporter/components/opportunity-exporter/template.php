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
<!-- usando uma tag <template> para o slot padrao -->
<mc-modal title="Título da modal">
    <template #default="modal">
        <p>conteúdo da modal</p>
        <a @click="modal.close()">você pode fechar a modal por aqui também</a>
    </template>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>

    <template #button-label="modal">
        <a href="#" @click="modal.open()"><?= i::__('Exportar Opportunidade') ?></a>
    </template>
</mc-modal>