<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
    panel--entity-actions
    panel--entity-tabs
    mc-loading
    mc-icon
');
?>
<panel--entity-tabs tabs="publish,draft,trash,archived" :type='type' :user="user.id" :select="newSelect">
    <template v-if="global.auth.is('saasSuperAdmin')" #entity-actions-left="{entity}">
        <mc-loading :condition="entity.recreatingPCache"><?= i::__('processando...') ?></mc-loading>
        <button v-if="!entity.recreatingPCache" class="button button-secondary button--sm" @click="recreatePCache(entity)">
            <mc-icon name="settings"></mc-icon>
            <?= i::__('recriar pcache') ?>
        </button>
    </template>
</panel--entity-tabs>
