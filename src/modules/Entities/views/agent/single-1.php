<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    agent-data-1
    country-address-view
    elderly-person
    mc-collapsible
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
    <div class="single-1__main-tabs">
        <mc-tabs class="tabs" sync-hash>
            <mc-tab icon="exclamation" label="<?= i::_e('Perfil') ?>" slug="info">
                <mc-container>
                    <div class="single-1__inner-tabs">
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
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="site" label="<?php i::_e('Site') ?>"></entity-data>
                                            </div>
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="telefone1" label="<?php i::_e('Telefone') ?>"></entity-data>
                                            </div>
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="emailPublico" label="<?php i::_e('E-mail público') ?>"></entity-data>
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
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" classes="col-12" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-data>
                                                <entity-data :entity="entity" classes="col-12" prop="raca" label="<?= i::__('Raça/Cor') ?>"></entity-data>
                                                <entity-data :entity="entity" classes="col-12" prop="comunidadesTradicional" label="<?= i::__('Comunidades tradicionais') ?>"></entity-data>
                                            </div>

                                            <div class="col-4 sm:col-12">
                                                <elderly-person :entity="entity"></elderly-person>
                                                <entity-data :entity="entity" classes="col-12" prop="genero" label="<?= i::__('Gênero') ?>"></entity-data>
                                                <entity-data :entity="entity" classes="col-12" prop="escolaridade" label="<?= i::__('Escolaridade') ?>"></entity-data>
                                            </div>

                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" classes="col-12" prop="orientacaoSexual" label="<?= i::__('Orientação Sexual') ?>"></entity-data>
                                                <entity-data :entity="entity" classes="col-12 pcd" prop="pessoaDeficiente" label="<?= i::__('Pessoa com Deficiência') ?>"></entity-data>
                                            </div>

                                            <entity-data :entity="entity" classes="col-12" prop="agenteItinerante" label="<?= i::__('É agente itinerante?') ?>"></entity-data>
                                            <entity-data :entity="entity" classes="col-12" prop="comunidadesTradicionalOutros" label="<?= i::__('Não encontrou sua comunidade Tradicional') ?>"></entity-data>

                                            <div class="col-12">
                                                <h3 class="bold"><?php i::_e("Outros documentos"); ?></h3>
                                                <p class="data-subtitle"><?php i::_e("Outros documentos"); ?></p>
                                                <entity-files-list v-if="entity.files.downloads != null" :entity="entity" classes="col-12" group="downloads" title="" hide-title></entity-files-list>
                                            </div>
                                        </div>
                                    </template>
                                </mc-card>
                            </mc-tab>

                            <mc-tab label="<?= i::_e('Endereço') ?>" slug="endereco">
                                <mc-card>
                                    <template #content>
                                        <div class="grid-12">
                                            <div class="col-12 address-display-simple">
                                                <country-address-view v-if="entity.publicLocation || entity.address || entity.endereco" :entity="entity" hide-label></country-address-view>
                                            </div>
                                        </div>
                                    </template>
                                </mc-card>
                            </mc-tab>

                            <mc-tab label="<?= i::_e('Administração') ?>" slug="administracao">
                                <mc-card>
                                    <template #content>
                                        <p class="single-1__administration-intro"><?php i::_e("Administradores do perfil podem visualizar e editar os dados públicos e pessoais do agente cultural que administram, além de fazer inscrições em seu nome nas oportunidades vinculadas na plataforma e transferir,editar e/ou excluir suas entidades. A administração dos perfis só e possivel mediante a autorização do proprietário do perfil."); ?></p>
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
                    </div>

                    <aside>
                        <div class="grid-12">
                            <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'before') ?>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'after') ?>

                        </div>
                    </aside>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Portfólio') ?>" slug="port">
                <mc-container>
                    <main>
                        <div class="single-1__inner-tabs">
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
                        </div>
                    </main>
                </mc-container>
            </mc-tab>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>