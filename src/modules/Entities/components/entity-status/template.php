<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
');
?>
<div v-if="entity.status != 1" class="entity-status">
    <mc-alert type="warning">
        <template v-if="entity.__objectType == 'agent'">
            <span v-if="entity.status == 0">
                <strong><?= i::__('Este agente está em rascunho.'); ?></strong>
                <?= i::__('Você precisa <strong>publicar</strong> para exibir para todas as pessoas.') ?>
            </span>
            <span v-if="entity.status == -10">
                <strong><?= i::__('Este agente está na lixeira.'); ?></strong>
                <?= i::__('Você pode <strong>recuperar</strong> ou <strong>excluir em definitivo</strong>') ?>
            </span>
            <span v-if="entity.status == -2">
            <strong><?= i::__('Este agente está arquivado.'); ?></strong>
                <?= i::__('Você pode <strong>publicar</strong> novamente para desarquivá-lo.') ?>
            </span>
        </template>

        <template v-if="entity.__objectType == 'space'">
            <span v-if="entity.status == 0">
                <strong><?= i::__('Este espaço está em rascunho.'); ?></strong>
                <?= i::__('Você precisa <strong>publicar</strong> para exibir para todas as pessoas.') ?>
            </span>
            <span v-if="entity.status == -10">
                <strong><?= i::__('Este espaço está na lixeira.'); ?></strong>
                <?= i::__('Você pode <strong>recuperar</strong> ou <strong>excluir em definitivo</strong>') ?>
            </span>
            <span v-if="entity.status == -2">
            <strong><?= i::__('Este espaço está arquivado.'); ?></strong>
                <?= i::__('Você pode <strong>publicar</strong> novamente para desarquivá-lo.') ?>
            </span>
        </template>

        <template v-if="entity.__objectType == 'event'">
            <span v-if="entity.status == 0">
                <strong><?= i::__('Este evento está em rascunho.'); ?></strong>
                <?= i::__('Você precisa <strong>publicar</strong> para exibir para todas as pessoas.') ?>
            </span>
            <span v-if="entity.status == -10">
                <strong><?= i::__('Este evento está na lixeira.'); ?></strong>
                <?= i::__('Você pode <strong>recuperar</strong> ou <strong>excluir em definitivo</strong>') ?>
            </span>
            <span v-if="entity.status == -2">
            <strong><?= i::__('Este evento está arquivado.'); ?></strong>
                <?= i::__('Você pode <strong>publicar</strong> novamente para desarquivá-lo.') ?>
            </span>
        </template>

        <template v-if="entity.__objectType == 'project'">
            <span v-if="entity.status == 0">
                <strong><?= i::__('Este projeto está em rascunho.'); ?></strong>
                <?= i::__('Você Você precisa <strong>publicar</strong> para exibir para todas as pessoas.') ?>
            </span>
            <span v-if="entity.status == -10">
                <strong><?= i::__('Este projeto está na lixeira.'); ?></strong>
                <?= i::__('Você pode <strong>recuperar</strong> ou <strong>excluir em definitivo</strong>') ?>
            </span>
            <span v-if="entity.status == -2">
            <strong><?= i::__('Este projeto está arquivado.'); ?></strong>
                <?= i::__('Você pode <strong>publicar</strong> novamente para desarquivá-lo.') ?>
            </span>
        </template>

        <template v-if="entity.__objectType == 'opportunity'">
            <span v-if="entity.status == 0">
                <strong><?= i::__('Este oportunidade está em rascunho.'); ?></strong>
                <?= i::__('Você Você precisa <strong>publicar</strong> para exibir para todas as pessoas.') ?>
            </span>
            <span v-if="entity.status == -10">
                <strong><?= i::__('Este oportunidade está na lixeira.'); ?></strong>
                <?= i::__('Você pode <strong>recuperar</strong> ou <strong>excluir em definitivo</strong>') ?>
            </span>
            <span v-if="entity.status == -2">
            <strong><?= i::__('Este oportunidade está arquivada.'); ?></strong>
                <?= i::__('Você pode <strong>publicar</strong> novamente para desarquivá-la.') ?>
            </span>
        </template>

    </mc-alert>
</div>