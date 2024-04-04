<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit
    create-project
    entity-actions
    entity-activity-card
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-owner
    entity-parent-edit
    entity-profile
    entity-related-agents
    entity-social-media
    entity-status
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-tabs
    mc-tab
');

$label = $this->isRequestedEntityMine() ? i::__('Meus projetos') : i::__('Projetos');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>

    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <mc-card class="feature">
                    <template #title>
                        <label><?php i::_e("Informações de Apresentação") ?></label>
                        <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
                    </template>
                    <template #content>
                        <div class="left">
                            <div class="grid-12 v-center">
                                <entity-cover :entity="entity" classes="col-12"></entity-cover>
                                <div class="col-12 grid-12">
                                    <?php $this->applyTemplateHook('entity-info','begin') ?>
                                    <div class="col-3 sm:col-12">
                                        <entity-profile :entity="entity"></entity-profile>
                                    </div>
                                    <div class="col-9 sm:col-12">
                                        <div class="grid-12">
                                            <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Nome do projeto") ?>" prop="name"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Tipo do projeto") ?>" prop="type"></entity-field>
                                        </div>
                                    </div>
                                    <?php $this->applyTemplateHook('entity-info','end') ?>
                                </div>
                                <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Link para página ou site do projeto") ?>" prop="site"></entity-field>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="right">
                            <entity-parent-edit :entity="entity" classes="col-12" type="project" label="<?php i::esc_attr_e('Adicione a um projeto principal') ?>"></entity-parent-edit>
                            <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                        </div>
                    </template>
                </mc-card>
                <main>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Período de execução do projeto"); ?></label>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Data inicial') ?>" prop="startsOn"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Data Final') ?>" prop="endsOn"></entity-field>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Contatos do projeto"); ?></label>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" prop="emailPublico"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="emailPrivado"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="telefonePublico"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="Telefone privado 1" prop="telefone1"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="Telefone privado 2" prop="telefone2"></entity-field>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Mais informações públicas"); ?></label>
                            <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Descrição') ?>" prop="longDescription"></entity-field>
                                <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?= i::_e('Adicionar arquivos para download') ?>" editable></entity-files-list>
                                <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
                                <entity-gallery-video :entity="entity" classes="col-12" title="<?php i::_e('Adicionar vídeos') ?>" editable></entity-gallery-video>
                                <entity-gallery :entity="entity" classes="col-12" title="<?php i::_e('Adicionar fotos na galeria') ?>" editable></entity-gallery>
                            </div>
                        </template>
                    </mc-card>
                </main>
                <aside>
                    <mc-card>
                        <template #content>
                            <div class="grid-12">
                                <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                                <entity-terms :entity="entity" editable classes="col-12" taxonomy="tag" title="<?php i::_e('Tags') ?>"></entity-terms>
                                <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                                <entity-owner :entity="entity" classes="col-12" title="<?php i::_e('Publicado por') ?>" editable></entity-owner>
                            </div>
                        </template>
                    </mc-card>
                </aside>
            </mc-container>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>
    
    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>