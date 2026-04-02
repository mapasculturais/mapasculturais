<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-tabs
    mc-alert
    oc-upload
')
?>

<div class="oc-entities">
    <oc-tabs :entity="entity" :groups="tabGroups" initial-group="tabs" sotarege-ref="entities">

        <template #entitiesSection="{tab, entity}">
            <?php $this->part('text-image-entities--section') ?>

            <div class="btn-entity-actions">
                <oc-actions :entity="entity" editable></oc-actions>
            </div>
        </template>

        <template #opportunity="{tab}">
            <div class="entities-tabs"></div>

            <oc-text-image :entity="entity" slug="opportunity">
                <template #opportunity-text="{tab, entity}">
                    <?php $this->part('text-image-entities--opportunity-text') ?>
                </template>

                <template #opportunity-image="{tab, entity}">
                    <mc-alert type="warning">
                        <?= i::__('Configure aqui a imagem do card de Oportunidades na Home. Certifique-se de que a imagem tenha as dimensões de ') ?><span class="color-red"><?= i::__('800x320') ?></span><?= i::__(', mantendo a proporção de 5:2') ?>
                    </mc-alert>

                    <oc-upload :entity="entity" prop="home-opportunities" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
                </template>
            </oc-text-image>
        </template>

        <template #event="{tab}">
            <div class="entities-tabs"></div>

            <oc-text-image :entity="entity" slug="event">
                <template #event-text="{tab, entity}">
                    <?php $this->part('text-image-entities--event-text') ?>
                </template>

                <template #event-image="{tab, entity}">
                    <mc-alert type="warning">
                        <?= i::__('Configure aqui a imagem do card de Eventos na Home. Certifique-se de que a imagem tenha as dimensões de ') ?><span class="color-red"><?= i::__('800x320') ?></span><?= i::__(', mantendo a proporção de 5:2') ?>
                    </mc-alert>

                    <oc-upload :entity="entity" prop="home-events" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
                </template>
            </oc-text-image>
        </template>

        <template #space="{tab}">
            <div class="entities-tabs"></div>

            <oc-text-image :entity="entity" slug="space">
                <template #space-text="{tab, entity}">
                    <?php $this->part('text-image-entities--space-text') ?>
                </template>

                <template #space-image="{tab, entity}">
                    <mc-alert type="warning">
                        <?= i::__('Configure aqui a imagem do card de Espaços na Home. Certifique-se de que a imagem tenha as dimensões de ') ?><span class="color-red"><?= i::__('800x320') ?></span><?= i::__(', mantendo a proporção de 5:2') ?>
                    </mc-alert>

                    <oc-upload :entity="entity" prop="home-spaces" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
                </template>
            </oc-text-image>
        </template>

        <template #agent="{tab}">
            <div class="entities-tabs"></div>

            <oc-text-image :entity="entity" slug="agent">
                <template #agent-text="{tab, entity}">
                    <?php $this->part('text-image-entities--agent-text') ?>
                </template>

                <template #agent-image="{tab, entity}">
                    <mc-alert type="warning">
                        <?= i::__('Configure aqui a imagem do card de Espaços na Home. Certifique-se de que a imagem tenha as dimensões de ') ?><span class="color-red"><?= i::__('800x320') ?></span><?= i::__(', mantendo a proporção de 5:2') ?>
                    </mc-alert>

                    <oc-upload :entity="entity" prop="home-agents" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
                </template>
            </oc-text-image>
        </template>

        <template #project="{tab}">
            <div class="entities-tabs"></div>

            <oc-text-image :entity="entity" slug="project">
                <template #project-text="{tab, entity}">
                    <?php $this->part('text-image-entities--project-text') ?>
                </template>

                <template #project-image="{tab, entity}">
                    <mc-alert type="warning">
                        <?= i::__('Configure aqui a imagem do card de Espaços na Home. Certifique-se de que a imagem tenha as dimensões de ') ?><span class="color-red"><?= i::__('800x320') ?></span><?= i::__(', mantendo a proporção de 5:2') ?>
                    </mc-alert>

                    <oc-upload :entity="entity" prop="home-projects" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
                </template>
            </oc-text-image>
        </template>

    </oc-tabs>
</div>