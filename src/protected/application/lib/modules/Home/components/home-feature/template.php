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
                            <entity-card :entity="entity" portrait slice-description>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        {{text(entity.__objectType)}}
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
                <mc-tab v-if="agents.length > 0" label="<?= i::esc_attr__('Agentes') ?>" slug="agents">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in agents" :key="entity.__objectId">
                            <entity-card :entity="entity" slice-description>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Agente') ?>
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
                <mc-tab v-if="spaces.length > 0" label="<?= i::esc_attr__('Espaços') ?>" slug="spaces">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in spaces" :key="entity.__objectId">
                            <entity-card :entity="entity" slice-description>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Espaço') ?>
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
                <mc-tab v-if="projects.length > 0" label="<?= i::esc_attr__('Projetos') ?>" slug="projects">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in projects" :key="entity.__objectId">
                            <entity-card :entity="entity" slice-description>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        <?= i::__('Projeto') ?>
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