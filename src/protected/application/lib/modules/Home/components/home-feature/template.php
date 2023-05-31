<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	entity-card 
    mc-loading
    mc-tab
	mc-tabs
');
?>
<div class="home-feature">
    <div class="home-feature__header">
        <div class="home-feature__header title">
            <label><?= $this->text('title', i::__('Em destaque')) ?></label>
        </div>
        <div class="home-feature__header description">
            <label><?= $this->text('description', i::__('Confira os últimos destaques de cada uma das entidades')) ?></label>
        </div>
    </div>
    <div class="home-feature__content">
        <div class="home-feature__content cards">
            <mc-loading :condition="!entities.length"></mc-loading>
            <mc-tabs v-if="entities.length > 0">
                <mc-tab label="<?= i::esc_attr__('Todos') ?>" slug="all">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.__objectId">
                            <entity-card :entity="entity" portrait>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Todos') ?>
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="entities.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </mc-tab>
                <mc-tab label="<?= i::esc_attr__('Agentes') ?>" slug="agents" v-if="agents.length > 0">
                    <carousel v-if="agents.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in agents" :key="entity.__objectId">
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Agentes') ?>
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="agents.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </mc-tab>
                <mc-tab label="<?= i::esc_attr__('Espaços') ?>" slug="spaces">
                    <carousel v-if="spaces.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in spaces" :key="entity.__objectId">
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Espaços') ?>
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="spaces.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </mc-tab>
                <mc-tab label="<?= i::esc_attr__('Projetos') ?>" slug="projects">
                    <carousel v-if="projects.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in projects" :key="entity.__objectId">
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Projetos') ?>
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="projects.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </mc-tab>
            </mc-tabs>
        </div>
    </div>
</div>