<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    agent-data-1
    collapsible-content
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
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
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
    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Perfil') ?>" slug="info">
            <mc-container>
                <mc-tabs class="tabs" sync-hash>
                    <mc-tab label="<?= i::_e('Público') ?>" slug="publico">
                        <div class="single-1__presentation-card">
                            <p><?php i::_e('Apresentação'); ?></p>
                            <div class="single-1__presentation-content">

                                <div class="single-1__presentation-item">
                                    <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area', 'before') ?>
                                    <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação'); ?>"></entity-terms>
                                    <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area', 'after') ?>
                                </div>

                                <div class="single-1__presentation-item">
                                    <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao', 'before') ?>
                                    <entity-terms :entity="entity" hide-required taxonomy="funcao" classes="col-12" title="<?php i::_e('Funções'); ?>"></entity-terms>
                                    <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao', 'after') ?>
                                </div>

                                <div class="single-1__presentation-item">
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'before') ?>
                                    <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'after') ?>
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
                                        <entity-data :entity="entity" prop="telefone1" label="<?php i::_e('Telefone') ?>"></entity-data>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 single-1__social-media">
                            <mc-card>
                                <template #content>
                                    <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
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
                                            <collapsible-content v-if="entity.spaces?.length>0" classes="col-12 single-1__connection-item">
                                                <template #header>
                                                    <mc-title tag="h4" size="medium" class="bold"><?php i::_e('Espaços'); ?></mc-title>
                                                </template>
                                                <template #body>
                                                    <entity-list title="" type="space" :ids="entity.spaces"></entity-list>
                                                </template>
                                            </collapsible-content>
                                            
                                            <collapsible-content v-if="entity.events?.length>0" classes="col-12 single-1__connection-item">
                                                <template #header>
                                                    <mc-title tag="h4" size="medium" class="bold"><?php i::_e('Eventos'); ?></mc-title>
                                                </template>
                                                <template #body>
                                                    <entity-list title="" type="event" :ids="entity.events"></entity-list>
                                                </template>
                                            </collapsible-content>
                                            
                                            <collapsible-content v-if="entity.children?.length>0" classes="col-12 single-1__connection-item">
                                                <template #header>
                                                    <mc-title tag="h4" size="medium" class="bold"><?php i::_e('Agentes'); ?></mc-title>
                                                </template>
                                                <template #body>
                                                    <entity-list title="" type="agent" :ids="entity.children"></entity-list>
                                                </template>
                                            </collapsible-content>
                                            
                                            <collapsible-content v-if="entity.projects?.length>0" classes="col-12 single-1__connection-item">
                                                <template #header>
                                                    <mc-title tag="h4" size="medium" class="bold"><?php i::_e('Projetos'); ?></mc-title>
                                                </template>
                                                <template #body>
                                                    <entity-list title="" type="project" :ids="entity.projects"></entity-list>
                                                </template>
                                            </collapsible-content>
                                        </div>
                                    </div>
                                </template>
                            </mc-card>
                        </div>
                        <div class="col-12">
                            <?php $this->applyTemplateHook('single1-entity-info-entity-seals', 'before') ?>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            <?php $this->applyTemplateHook('single1-entity-info-entity-seals', 'after') ?>
                        </div>
                        <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>

                    </mc-tab>

                    <mc-tab label="<?= i::_e('Dados pessoais') ?>" slug="dados-pessoais">
                        <mc-card>
                            <template #content>
                                <div class="grid-12">
                                    <agent-data-1 :entity="entity"></agent-data-1>
                                </div>
                            </template>
                        </mc-card>
                    </mc-tab>

                    <mc-tab label="<?= i::_e('Endereço') ?>" slug="endereco">
                        <mc-card>
                            <template #content>
                                <div class="grid-12">
                                    <entity-field :disabled="true" classes="col-4 sm:col-12" :entity="entity" prop="En_CEP"></entity-field>
                                    <entity-field :disabled="true" classes="col-8 sm:col-12" :entity="entity" prop="En_Nome_Logradouro"></entity-field>
                                    <entity-field :disabled="true" classes="col-2 sm:col-4" :entity="entity" prop="En_Num"></entity-field>
                                    <entity-field :disabled="true" classes="col-10 sm:col-8" :entity="entity" prop="En_Bairro"></entity-field>
                                    <entity-field :disabled="true" classes="col-12" :entity="entity" prop="En_Complemento" label="<?php i::_e('Complemento ou ponto de referência') ?>"></entity-field>
                                    <country-address-view v-if="entity.publicLocation" :entity="entity" class="col-12"></country-address-view>
                                </div>
                            </template>
                        </mc-card>
                    </mc-tab>

                    <mc-tab label="<?= i::_e('Administração') ?>" slug="administracao">
                        <mc-card>
                            <template #content>
                                <p><?php i::_e("Administradores do perfil podem visualizar e editar os dados públicos e pessoais do agente cultural que administram, além de fazer inscrições em seu nome nas oportunidades vinculadas na plataforma e transferir,editar e/ou excluir suas entidades. A administração dos perfis só e possivel mediante a autorização do proprietário do perfil."); ?></p>
                                <div class="grid-12">
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'before') ?>
                                    <entity-admins :entity="entity" classes="col-12"></entity-admins>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'after') ?>

                                    <?php $this->applyTemplateHook('single1-entity-info-entity-owner', 'before') ?>
                                    <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-owner', 'after') ?>
                                </div>
                            </template>
                        </mc-card>
                    </mc-tab>
                </mc-tabs>

                <aside>
                    <div class="grid-12">
                        <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'before') ?>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                        <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'after') ?>

                    </div>
                </aside>
            </mc-container>
        </mc-tab>

        <mc-tab label="<?= i::esc_attr_e('PortFólio') ?>" slug="port">
            <mc-container>
                <main>
                    <mc-tabs class="tabs" sync-hash>
                        <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                            <mc-card>
                                <template #content>
                                    <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download'); ?>"></entity-files-list>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                            <mc-card>
                                <template #content>
                                    <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::esc_attr_e('Videos') ?>" slug="videos">
                            <mc-card>
                                <template #content>
                                    <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                            <mc-card>
                                <template #content>
                                    <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                                </template>
                            </mc-card>
                        </mc-tab>
                    </mc-tabs>
                </main>
            </mc-container>
        </mc-tab>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>