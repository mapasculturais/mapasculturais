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
    entity-people-collaborators
    entity-people-owner
    entity-profile
    entity-project-parent
    entity-renew-lock
    entity-social-media
    entity-status
    entity-terms
    mc-breadcrumb
    mc-collapsible
    mc-container
    mc-tabs
    mc-tab
');

$label = $this->isRequestedEntityMine() ? i::__('Meus projetos') : i::__('Projetos');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'edit', [$entity->id])],
];

$include_pending_relations = $entity->canUser('viewPrivateData')
    || $entity->canUser('createAgentRelation')
    || $entity->canUser('removeAgentRelation')
    || $entity->canUser('@control');

$collaborator_count = 0;
$admin_count = 0;
foreach ($entity->getAgentRelationsGrouped(null, $include_pending_relations) as $group => $relations) {
    if ($group === 'group-admin') {
        $admin_count = count($relations);
        continue;
    }

    if ($group === '@support') {
        continue;
    }

    $collaborator_count += count($relations);
}

$owner_count = $entity->owner ? 1 : 0;
?>

<div class="main-app edit-1 single-1">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>

    <div class="single-1__main-tabs">
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs', 'begin') ?>

        <mc-tab label="<?= i::_e('Dados gerais') ?>" slug="info">
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <main class="edit-1__perfil-main">
                    <?php $this->applyTemplateHook('main-mc-card', 'begin') ?>
                    <p class="edit-1__required-hint"><?php i::_e('Campos marcados com * são de preenchimento obrigatório.'); ?></p>

                    <div class="stack--sm">
                        <div class="edit-1__section">
                            <entity-project-parent :entity="entity" classes="col-12"></entity-project-parent>
                        </div>

                        <div class="edit-1__section">
                            <mc-collapsible :open="true">
                                <template #header>
                                    <div class="edit-1__section-heading">
                                        <h3 class="edit-1__section-title"><?php i::_e('Informações de apresentação') ?></h3>
                                        <p class="edit-1__section-subtitle"><?php i::_e('Os dados inseridos abaixo serão exibidos para todos os usuários da plataforma.') ?></p>
                                    </div>
                                </template>
                                <template #body>
                                    <div class="grid-12 v-bottom">
                                        <entity-cover :entity="entity" classes="col-12"></entity-cover>

                                        <div class="col-12 grid-12">
                                            <?php $this->applyTemplateHook('entity-info', 'begin') ?>
                                            <div class="col-3 sm:col-12">
                                                <entity-profile :entity="entity"></entity-profile>
                                            </div>
                                            <div class="col-12 sm:col-12 grid-12 v-bottom">
                                                <entity-field :entity="entity" classes="col-12" prop="name" label="<?php i::_e('Nome do projeto') ?>"></entity-field>
                                                <entity-field :entity="entity" classes="col-12" prop="type" label="<?php i::_e('Tipo do projeto') ?>"></entity-field>
                                            </div>
                                            <?php $this->applyTemplateHook('entity-info', 'end') ?>
                                        </div>

                                        <entity-terms :entity="entity" taxonomy="tag" editable classes="col-12" title="<?php i::_e('Tags'); ?>"></entity-terms>

                                        <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400" label="<?php i::_e('Descrição curta') ?>"></entity-field>
                                        <?php $this->applyTemplateHook('public-info-long-description', 'before') ?>
                                        <entity-field :entity="entity" classes="col-12" prop="longDescription" label="<?php i::_e('Descrição longa') ?>"></entity-field>
                                        <?php $this->applyTemplateHook('public-info-long-description', 'after') ?>

                                        <entity-field :entity="entity" classes="col-12" prop="site" label="<?php i::_e('Link (URL)') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="emailPublico" label="<?php i::_e('E-mail público') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefonePublico" label="<?php i::_e('Telefone público (com DDD)') ?>"></entity-field>
                                    </div>
                                </template>
                            </mc-collapsible>
                        </div>

                        <div class="edit-1__section">
                            <mc-collapsible :open="true">
                                <template #header>
                                    <div class="edit-1__section-heading">
                                        <h3 class="edit-1__section-title"><?php i::_e('Período de execução') ?></h3>
                                        <p class="edit-1__section-subtitle"><?php i::_e('Os dados inseridos abaixo serão exibidos para todos os usuários da plataforma.') ?></p>
                                    </div>
                                </template>
                                <template #body>
                                    <div class="grid-12">
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="startsOn" label="<?php i::_e('Data de início') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="endsOn" label="<?php i::_e('Data de fim') ?>"></entity-field>
                                    </div>
                                </template>
                            </mc-collapsible>
                        </div>

                        <div class="edit-1__section">
                            <mc-collapsible :open="false">
                                <template #header>
                                    <div class="edit-1__section-heading">
                                        <h3 class="edit-1__section-title"><?php i::_e('Dados de contato privado') ?></h3>
                                        <p class="edit-1__section-subtitle"><?php i::_e('Não se preocupe, esses dados não serão exibidos publicamente.') ?></p>
                                    </div>
                                </template>
                                <template #body>
                                    <div class="grid-12">
                                        <entity-field :entity="entity" classes="col-12" prop="emailPrivado" label="<?php i::_e('E-mail privado') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone1" label="<?php i::_e('Telefone privado 1') ?>"></entity-field>
                                        <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone2" label="<?php i::_e('Telefone privado 2') ?>"></entity-field>
                                    </div>
                                </template>
                            </mc-collapsible>
                        </div>

                        <div class="edit-1__section edit-1__section--social">
                            <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                        </div>
                    </div>
                    <?php $this->applyTemplateHook('main-mc-card', 'end') ?>
                </main>
            </mc-container>
        </mc-tab>

        <mc-tab label="<?= i::esc_attr_e('Portfólio') ?>" slug="port">
            <mc-container>
                <main>
                    <div class="edit-1__portfolio edit-1__inner-tabs">
                        <mc-tabs class="tabs" sync-hash>
                            <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                                <div class="edit-1__portfolio-card">
                                    <entity-files-list
                                        :entity="entity"
                                        classes="portfolio-files-list"
                                        group="downloads"
                                        title="<?php i::esc_attr_e('Arquivos para download'); ?>"
                                        editable
                                        hide-title
                                        button-primary>
                                    </entity-files-list>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                                <div class="edit-1__portfolio-card">
                                    <entity-links
                                        :entity="entity"
                                        title="<?php i::_e('Links'); ?>"
                                        editable
                                        hide-title
                                        button-primary>
                                    </entity-links>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                <div class="edit-1__portfolio-card">
                                    <entity-gallery-video
                                        :entity="entity"
                                        title="<?php i::esc_attr_e('Vídeos'); ?>"
                                        editable
                                        hide-title
                                        button-primary>
                                    </entity-gallery-video>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                <div class="edit-1__portfolio-card">
                                    <entity-gallery
                                        :entity="entity"
                                        title="<?php i::esc_attr_e('Fotos'); ?>"
                                        editable
                                        hide-title
                                        button-primary>
                                    </entity-gallery>
                                </div>
                            </mc-tab>
                        </mc-tabs>
                    </div>
                </main>
            </mc-container>
        </mc-tab>

        <mc-tab label="<?= i::esc_attr_e('Pessoas') ?>" slug="pessoas">
            <mc-container>
                <main>
                    <div class="single-1__people single-1__inner-tabs">
                        <mc-tabs class="tabs" sync-hash default-tab="colaboradores">
                            <template #header="{ tab }">
                                <span>{{ tab.label }}</span>
                                <span v-if="tab.meta?.count > 0" class="single-1__connections-count">
                                    {{ tab.meta.count }}
                                </span>
                            </template>

                            <mc-tab
                                label="<?= i::esc_attr_e('Colaboradores') ?>"
                                :meta="{ count: <?= (int) $collaborator_count ?> }"
                                slug="colaboradores">
                                <div class="single-1__people-collaborators">
                                    <entity-people-collaborators
                                        :entity="entity"
                                        editable
                                        empty-message="<?php i::esc_attr_e('Você ainda não adicionou nenhum colaborador neste projeto.') ?>">
                                    </entity-people-collaborators>
                                </div>
                            </mc-tab>

                            <mc-tab
                                label="<?= i::esc_attr_e('Administradores') ?>"
                                :meta="{ count: <?= (int) $admin_count ?> }"
                                slug="administradores">
                                <div class="single-1__administration-card">
                                    <h2 class="single-1__administration-title"><?php i::_e('Administradores do perfil'); ?></h2>
                                    <p class="single-1__administration-intro">
                                        <?php i::_e('Administradores do perfil podem visualizar e editar os dados públicos do projeto que administram, além de transferir, editar e/ou excluir a entidade. A administração dos perfis só é possível mediante a autorização do proprietário do perfil.'); ?>
                                    </p>
                                    <entity-admins
                                        :entity="entity"
                                        variant="edit"
                                        editable
                                        classes="single-1__administration-admins"></entity-admins>
                                </div>
                            </mc-tab>

                            <mc-tab
                                label="<?= i::esc_attr_e('Proprietário(a)') ?>"
                                :meta="{ count: <?= (int) $owner_count ?> }"
                                slug="proprietario">
                                <div class="single-1__people-owner">
                                    <entity-people-owner
                                        :entity="entity"
                                        empty-message="<?php i::esc_attr_e('Esse projeto não possui proprietário.') ?>">
                                    </entity-people-owner>
                                </div>
                            </mc-tab>
                        </mc-tabs>
                    </div>
                </main>
            </mc-container>
        </mc-tab>

        <?php $this->applyTemplateHook('tabs', 'end') ?>
    </mc-tabs>
    </div>

    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>
