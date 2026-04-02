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
    entity-field
')
?>
<div class="complementary-tabs">
    <oc-tabs :entity="entity" :groups="tabGroups" initial-group="tabs" sotarege-ref="complementary">
        <template #logo="{tab}">
            <?php $this->part('text-image-complementary--logo') ?>
        </template>

        <template #faviconSvg="{tab}">
            <?php $this->part('text-image-complementary--faviconSvg') ?>
        </template>

        <template #faviconPng="{tab}">
            <?php $this->part('text-image-complementary--faviconPng') ?>
        </template>

        <template #share="{tab}">
            <?php $this->part('text-image-complementary--share') ?>
        </template>

        <template #imgMail="{tab}">
            <?php $this->part('text-image-complementary--mail') ?>
        </template>
    </oc-tabs>
</div>