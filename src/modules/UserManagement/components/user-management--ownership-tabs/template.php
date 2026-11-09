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
<panel--entity-tabs tabs="publish,draft,granted,trash,archived" :type='type' :user="user.id" :select="newSelect" :limit="20" :order="defaultOrder">
    <template #card-content="{entity}">
        <span v-if="type == 'agent' && global.showIds.agent" class="uppercase semibold entity-card__id">
            Id: <span class="bold">{{entity.id}}</span>
        </span>
        <span v-if="entity.type">
            <?= i::__('Tipo: ') ?> <strong>{{ entity.type.name }}</strong>
        </span>
    </template>

    <template  #entity-actions-left="{entity}">
        <div v-if="type == 'agent' && !entity.parent">
            <mc-icon name="agent-default-account-user"></mc-icon>
        </div>
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

        <?php $this->applyComponentHook('entity-actions-left', 'end') ?>
    </template>
</panel--entity-tabs>
