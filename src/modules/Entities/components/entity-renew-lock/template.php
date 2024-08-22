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

<div class="is-locked">
    <mc-modal ref="modalBlock" title="<?= i::__('Edição bloqueada') ?>" :close-button='false' :esc-to-close="false" :click-to-close="false">
        <div class="content">
            <p> <?= i::__('Outro usuário assumiu o controle e está editando esta entidade') ?> </p>
        </div>
        <template #actions="modal">
            <button class="button button--primary" @click="unlock(modal)"><?= i::__('Assumir controle') ?></button>
            <button class="button button--text button--text-del" @click="exit"><?= i::__('Sair') ?></button>
        </template>
        <template #button> <div class="is-locked__utton"></div> </template>
    </mc-modal>
</div>
