<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit 
    country-address-form
    entity-actions
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-location
    entity-owner
    entity-profile
    entity-related-agents
    entity-renew-lock
    entity-social-media
    entity-status
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-tabs
    mc-tab
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>

    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs', 'begin') ?>

        <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
            <mc-container class="single-1">
                <entity-status :entity="entity"></entity-status>
                <mc-card class="feature">
                    <template #content>
                        <mc-tabs class="tabs" sync-hash>
                            <mc-tab label="<?= i::esc_attr_e('Público') ?>" slug="publico">
                                <mc-card>
                                    <div class="grid-12 v-bottom">
                                        <entity-cover :entity="entity" classes="col-12"></entity-cover>

                                        <div class="col-12 grid-12">
                                            <?php $this->applyTemplateHook('entity-info', 'begin') ?>
                                            <div class="col-3 sm:col-12">
                                                <entity-profile :entity="entity"></entity-profile>
                                            </div>
                                            <div class="col-9 sm:col-12 grid-12 v-bottom">
                                                <entity-field :entity="entity" classes="col-12" prop="name" label="<?php i::_e('Nome do Agente') ?>"></entity-field>
                                            </div>
                                            <?php $this->applyTemplateHook('entity-info', 'end') ?>
                                        </div>
                                        <?php $this->applyTemplateHook('edit2-entity-info-taxonomie-area', 'before') ?>
                                        <entity-terms :entity="entity" taxonomy="area" editable classes="col-12" title="<?php i::_e('Área de atuação'); ?>"></entity-terms>
                                        <?php $this->applyTemplateHook('edit2-entity-info-taxonomie-area', 'after') ?>

                                        <entity-terms :entity="entity" taxonomy="tag" classes="col-12" title="Tags" editable></entity-terms>

                                        <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400"></entity-field>
                                        <entity-field :entity="entity" classes="col-6" prop="site" label="<?php i::_e('Link (URL)') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone1" label="<?= i::__('Telefone privado 1 com DDD') ?>"></entity-field>
                                    </div>

                                </mc-card>
                                 <div class="card-separator"></div>
                                <mc-card class="social-media-card">
                                    <template #content>
                                        <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                                    </template>
                                </mc-card>
                            </mc-tab>

                            <mc-tab label="<?= i::esc_attr_e('Dados organizacionais') ?>" slug="org">
                                <mc-card>
                                    <template #title>
                                        <label><?php i::_e("Dados do Agente Coletivo"); ?></label>
                                        <p class="data-subtitle"><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente") ?></p>
                                    </template>
                                    <template #content>
                                        <div class="grid-12">
                                            <entity-field :entity="entity" classes="col-9 sm:col-12" prop="nomeSocial" label="<?php i::_e('Nome Fantasia') ?>"></entity-field>
                                            <entity-field :entity="entity" classes="col-9 sm:col-12" prop="nomeCompleto" label="<?php i::_e('Razão Social') ?>"></entity-field>
                                            <entity-field v-if="global.auth.is('admin')" :entity="entity" prop="type" @change="entity.save(true).then(() => global.reload())" classes="col-12"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" prop="cnpj" label="CNPJ"></entity-field>
                                            <entity-field :disabled="!(entity?.cnpj?.length == 18)" :entity="entity" classes="col-12" prop="cnpjAnexo" title-modal="<?php i::_e('Anexar CNPJ - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnpj" :hide-label="true"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" prop="dataDeNascimento" label="<?= i::__('Data de fundação') ?>"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" prop="emailPrivado" label="<?= i::__('E-mail privado ') ?>"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" prop="telefonePublico" label="<?= i::__('Telefone público com DDD') ?>"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" prop="emailPublico" label="<?= i::__('E-mail público') ?>"></entity-field>
                                            <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone2" label="<?= i::__('Telefone privado 2 com DDD') ?>"></entity-field>
                                            <div class="col-12 divider"></div>

                                        </div>
                                    </template>
                                </mc-card>
                            </mc-tab>

                            <mc-tab label="<?= i::esc_attr_e('Endereço') ?>" slug="endereco">
                                <mc-card>
                                    <country-address-form :entity="entity" class="col-12"></country-address-form>
                                </mc-card>
                            </mc-tab>


                        </mc-tabs>
                        <div class="divider"></div>
                    </template>
                </mc-card>

            </mc-container>

            <!--  <main>
                INFORMAÇÕES PUBLICAS
                <mc-card>
                    <template #title>
                        <label><?php i::_e("informações públicas"); ?></label>
                        <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                    </template>
                    <template #content>
                        <div class="grid-12">
                            <entity-field :entity="entity" classes="col-12" prop="longDescription" editable></entity-field>
                            <entity-files-list :entity="entity" classes="col-12" group="downloads" title="Adicionar arquivos para download" editable></entity-files-list>
                            <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
                            <entity-gallery-video title="<?php i::_e('Adicionar vídeos') ?>" :entity="entity" classes="col-12" editable></entity-gallery-video>
                            <entity-gallery title="<?php i::_e('Adicionar fotos na galeria') ?>" :entity="entity" classes="col-12" editable></entity-gallery>
                        </div>
                    </template>
                </mc-card>
            </main> -->
            <aside>
                <mc-card>
                    <template #content>
                        <div class="grid-12">
                            <!--  <entity-admins :entity="entity" classes="col-12" editable></entity-admins> -->

                            <!--   <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents> -->
                            <!--  <entity-owner :entity="entity" title="Publicado por" classes="col-12" editable></entity-owner>
                        </div> -->
                    </template>
                </mc-card>
            </aside>
            </mc-container>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs', 'end') ?>
    </mc-tabs>

    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>