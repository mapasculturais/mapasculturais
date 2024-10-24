<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit 
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
    entity-parent-edit
    entity-profile
    entity-related-agents
    entity-renew-lock
    entity-social-media
    entity-status
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    permission-publish
    mc-tabs
    mc-tab
');

$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Espaços'), 'url' => $app->createUrl('panel', 'spaces')],
    ['label' => $entity->name, 'url' => $app->createUrl('space', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>

    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <mc-card class="feature">
                    <template #title>
                        <label class="card__title--title"><?php i::_e("Informações de Apresentação") ?></label>
                        <p class="card__title--description"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
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
                                            <entity-field :entity="entity" classes="col-12" label="Nome do espaço" prop="name"></entity-field>
                                            <entity-field :entity="entity" classes="col-12" label="Tipo do espaço" prop="type"></entity-field>
                                        </div>
                                    </div>
                                    <?php $this->applyTemplateHook('entity-info','end') ?>
                                </div>
                                <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="Link para página ou site do espaço" prop="site"></entity-field>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="right">
                            <entity-terms :entity="entity" classes="col-12" taxonomy="area" editable title="Área de atuação"></entity-terms>
                            <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                        </div>
                    </template>
                </mc-card>
                <main>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Endereço do espaço"); ?></label>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-location :entity="entity" classes="col-12" editable hide-label></entity-location>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Acessibilidade"); ?></label>
                        </template>
                        <template #content>
                            <entity-field :entity="entity" classes="col-12" prop="acessibilidade"></entity-field>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Acessibilidade física"); ?></label>
                        </template>
                        <template #content>
                            <entity-field :entity="entity" classes="col-12" type="multiselect" prop="acessibilidade_fisica"></entity-field>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Capacidade"); ?></label>
                        </template>
                        <template #content>
                            <entity-field :entity="entity" classes="col-12" prop="capacidade"></entity-field>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Horário e funcionamento"); ?></label>
                        </template>
                        <template #content>
                            <entity-field :entity="entity" classes="col-12" prop="horario"></entity-field>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Informações sobre o espaço"); ?></label>
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
                            <p><?= i::__('Apresente melhor o seu Espaço.'); ?> <br> <?= i::__('Adicione documentos, links, vídeos e imagens que contem a sua história.') ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" prop="longDescription" label="<?php i::_e('Descrição'); ?>"></entity-field>
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
                                <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="Tags" editable></entity-terms>
                                <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                                <permission-publish :entity="entity"></permission-publish>
                                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                                <entity-parent-edit :entity="entity" classes="col-12" type="space" label="<?php i::esc_attr_e('Adicionar Supra Espaço') ?>"></entity-parent-edit>
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