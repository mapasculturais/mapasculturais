<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    agent-data-2
    country-address-view
    complaint-suggestion
    entity-actions
    entity-admins
    entity-data
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-list
    entity-location
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-collapsible
    mc-container
    mc-share-links
    mc-tab
    mc-tabs
    mc-title
    opportunity-list
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <div class="single-1__main-tabs">
        <mc-tabs class="tabs" sync-hash>
            <mc-tab icon="exclamation" label="<?= i::_e('Perfil') ?>" slug="info" classes="tab-perfil">
                <mc-container>
                    <main class="flex-container">
                        <div class="single-1__inner-tabs">
                            <mc-tabs class="tabs" sync-hash>
                                <mc-tab label="<?= i::_e('Público') ?>" slug="publico">
                                    <div class="single-1__presentation-card">
                                        <p><?php i::_e('Apresentação'); ?></p>
                                        <div class="single-1__presentation-content">

                                            <div class="single-1__presentation-item">
                                                <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area', 'before') ?>
                                                <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação'); ?>"></entity-terms>
                                                <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area', 'after') ?>
                                            </div>

                                            <div class="single-1__presentation-item">
                                                <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag', 'before') ?>
                                                <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                                                <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag', 'after') ?>
                                            </div>

                                            <div v-if="entity.longDescription" class="col-12 single-1__presentation-item">
                                                <span>
                                                    <h3 class="single-1__description bold"><?php i::_e('Descrição'); ?></h3>
                                                </span>
                                                <p class="description" v-html="entity.longDescription"></p>
                                            </div>

                                            <div class="grid-12 single-1__presentation-item">
                                                <div class="col-6 sm:col-12">
                                                    <entity-data :entity="entity" prop="site" label="<?php i::_e('Site') ?>"></entity-data>
                                                </div>
                                                <div class="col-6 sm:col-12">
                                                    <entity-data :entity="entity" prop="telefonePublico" label="<?php i::_e('Telefone') ?>"></entity-data>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 single-1__social-media">
                                        <mc-card>
                                            <template #content>
                                                <?php $this->applyTemplateHook('single2-entity-info-social-media', 'before') ?>
                                                <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                                                <?php $this->applyTemplateHook('single2-entity-info-social-media', 'after') ?>
                                            </template>
                                        </mc-card>
                                    </div>

                                    <div class="col-12 single-1__connections">
                                        <mc-card>
                                            <template #content>
                                                <span>
                                                    <h3 class="single-1__description bold"><?php i::_e('Conexões'); ?></h3>
                                                </span>
                                                <opportunity-list></opportunity-list>
                                                <div class="grid-12 col-12">
                                                    <div v-if="entity.spaces?.length > 0 || entity.children?.length > 0 || entity.events?.length > 0 || entity.projects?.length > 0" class="col-12">
                                                        <mc-collapsible v-if="entity.spaces?.length>0" open class="col-12 single-1__connection-item">
                                                            <template #header>
                                                                <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Espaços'); ?></mc-title>
                                                            </template>
                                                            <template #body>
                                                                <entity-list title="" type="space" :ids="entity.spaces"></entity-list>
                                                            </template>
                                                        </mc-collapsible>

                                                        <mc-collapsible v-if="entity.events?.length>0" open class="col-12 single-1__connection-item">
                                                            <template #header>
                                                                <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Eventos'); ?></mc-title>
                                                            </template>
                                                            <template #body>
                                                                <entity-list title="" type="event" :ids="entity.events"></entity-list>
                                                            </template>
                                                        </mc-collapsible>

                                                        <mc-collapsible v-if="entity.children?.length>0" open class="col-12 single-1__connection-item">
                                                            <template #header>
                                                                <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Agentes'); ?></mc-title>
                                                            </template>
                                                            <template #body>
                                                                <entity-list title="" type="agent" :ids="entity.children"></entity-list>
                                                            </template>
                                                        </mc-collapsible>

                                                        <mc-collapsible v-if="entity.projects?.length>0" open class="col-12 single-1__connection-item">
                                                            <template #header>
                                                                <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Projetos'); ?></mc-title>
                                                            </template>
                                                            <template #body>
                                                                <entity-list title="" type="project" :ids="entity.projects"></entity-list>
                                                            </template>
                                                        </mc-collapsible>
                                                    </div>
                                                </div>
                                            </template>
                                        </mc-card>
                                    </div>
                                    <div class="col-12">
                                        <?php $this->applyTemplateHook('single2-entity-info-entity-seals', 'before') ?>
                                        <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                                        <?php $this->applyTemplateHook('single2-entity-info-entity-seals', 'after') ?>
                                    </div>
                                    <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>

                                </mc-tab>

                                <mc-tab label="<?= i::_e('Dados organizacionais') ?>" slug="dados-organizacionais">
                                    <mc-card>
                                        <template #content>
                                            <div class="grid-12">
                                                <agent-data-2 :entity="entity"></agent-data-2>
                                                <country-address-view v-if="entity.publicLocation" :entity="entity" class="col-12"></country-address-view>
                                            </div>
                                        </template>
                                    </mc-card>
                                </mc-tab>

                                <mc-tab label="<?= i::_e('Endereço') ?>" slug="endereco">
                                    <mc-card>
                                        <template #content>
                                            <div class="grid-12">
                                                <country-address-view v-if="entity.publicLocation" :entity="entity" class="col-12"></country-address-view>
                                            </div>
                                        </template>
                                    </mc-card>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>

                    <aside>
                        <div class="grid-12">
                            <?php $this->applyTemplateHook('single2-entity-info-entity-related-agents', 'before') ?>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-related-agents', 'after') ?>

                            <?php $this->applyTemplateHook('single2-entity-info-mc-share-links', 'before') ?>
                            <mc-share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                            <?php $this->applyTemplateHook('single2-entity-info-mc-share-links', 'after') ?>
                        </div>
                    </aside>
                </mc-container>
            </mc-tab>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>