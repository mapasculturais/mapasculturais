<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-link
    panel--entity-actions
    panel--entity-tabs
    mc-loading
    mc-icon
');
?>
<panel--entity-tabs tabs="publish,draft,trash,archived" :type='type' :user="user.id" :select="newSelect">
    <template  #entity-actions-left="{entity}">
        <div v-if="global.auth.is('admin') && entity.status >= 0">
            <mc-confirm-button @confirm="entity.delete()">
                <template #button="modal">
                    <button @click="modal.open()" class="button button--text delete button--icon button--sm panel__entity-actions--trash">
                        <mc-icon name="trash"></mc-icon>
                        <?php i::_e("Excluir") ?>
                    </button>
                </template>
                <template #message="message">
                    <?php i::_e('Você está certo que deseja excluir?') ?>
                </template>
            </mc-confirm-button>
        </div>

        <div v-if="global.auth.is('saasSuperAdmin')">
            <mc-loading :condition="entity.recreatingPCache"><?= i::__('processando...') ?></mc-loading>
            <button v-if="!entity.recreatingPCache" class="button button-secondary button--sm" @click="recreatePCache(entity)">
                <mc-icon name="settings"></mc-icon>
                <?= i::__('recriar pcache') ?>
            </button>
        </div>
    </template>
</panel--entity-tabs>
