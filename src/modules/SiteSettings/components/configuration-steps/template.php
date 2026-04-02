<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-header
    oc-steps
    oc-tabs
    oc-actions
    mc-entity 
');
?>

<mc-entity :id="settingsId" type="settings" select="*" v-slot="{ entity }">
    <div class="container">
        <div class="configuration-steps">
            <mc-card>
                <oc-header :entity="entity"></oc-header>

                <oc-steps :entity="entity"></oc-steps>
            </mc-card>
        </div>
    </div>

    <div class="menu">
        <oc-tabs :entity="entity" :groups="tabGroups" :initial-group="initialGroup()" sotarege-ref="initial">
            <template #email="{tab}">
                <?php $this->part('settings-email') ?>
            </template>

            <template #recaptcha="{tab}">
                <?php $this->part('settings-recaptcha') ?>
            </template>

            <template #georeferencing="{tab}">
                <?php $this->part('settings-georeferencing') ?>
            </template>

            <template #socialmedia="{tab}">
                <?php $this->part('settings-socialmedia') ?>
            </template>

            <template #banner="{tab}">
                <?php $this->part('text-image-banner') ?>
            </template>

            <template #entities="{tab}">
                <?php $this->part('text-image-entities') ?>
            </template>

            <template #feature="{tab}">
                <?php $this->part('text-image-feature') ?>
            </template>

            <template #register="{tab}">
                <?php $this->part('text-image-register') ?>
            </template>

            <template #map="{tab}">
                <?php $this->part('text-image-map') ?>
            </template>

            <template #developer>
                <?php $this->part('text-image-developer') ?>
            </template>

            <template #complementary>
                <?php $this->part('text-image-complementary') ?>
            </template>
            <template #colors>
                <?php $this->part('colors') ?>
            </template>
        </oc-tabs>
    </div>
</mc-entity>