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
    entity-people-collaborators
    entity-profile
    entity-renew-lock
    entity-related-agents
    entity-social-media
    entity-status
    entity-terms
    mc-breadcrumb
    mc-card
    mc-collapsible
    mc-container
    mc-tabs
    mc-tab
    mc-title
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];

// Contagens usadas na aba "Pessoas" (inclui relações pendentes apenas quando o usuário tem permissão).
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

$owner_count = $entity->parent ? 1 : 0;
?>

<div class="main-app edit-1 single-1">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <div class="single-1__main-tabs">
        <mc-tabs class="tabs" sync-hash>
            <?php $this->applyTemplateHook('tabs', 'begin') ?>
            <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
                <?php $this->applyTemplateHook('entity-info-validation', 'begin') ?>
                <mc-container>
                    <entity-status :entity="entity"></entity-status>
                    <main class="edit-1__perfil-main single-1__perfil-main">
                        <div class="stack--sm">
                        <div class="edit-1__section">
                            <entity-people-collaborators
                                :entity="entity"
                                preview
                                manage
                                classes="single-1__people-preview-wrap"></entity-people-collaborators>
                        </div>

                        <div class="edit-1__section">
                        <mc-collapsible :open="true">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Informações de Apresentação") ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
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
                                        <div class="col-9 sm:col-12 grid-12 v-bottom">
                                            <entity-field :entity="entity" classes="col-12" prop="name" label="<?php i::_e('Nome do Agente') ?>"></entity-field>
                                        </div>
                                        <?php $this->applyTemplateHook('entity-info', 'end') ?>
                                    </div>

                                    <?php $this->applyTemplateHook('edit2-entity-info-taxonomie-area', 'before') ?>
                                    <entity-terms :entity="entity" taxonomy="area" editable classes="col-12" title="<?php i::_e('Área de atuação'); ?>"></entity-terms>
                                    <entity-terms :entity="entity" taxonomy="tag" classes="col-12" title="Tags" editable></entity-terms>
                                    <?php $this->applyTemplateHook('edit2-entity-info-taxonomie-area', 'after') ?>

                                    <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400"></entity-field>
                                    <entity-field :entity="entity" classes="col-12" prop="longDescription" editable></entity-field>
                                    <entity-field :entity="entity" classes="col-6" prop="site" label="<?php i::_e('Link (URL)') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone1" label="<?= i::__('Telefone privado 1 com DDD') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-12" prop="emailPublico" label="<?= i::__('E-mail público') ?>"></entity-field>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Dados do Agente Coletivo"); ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente") ?></p>
                                </div>
                            </template>
                            <template #body>
                                <div class="grid-12">
                                    <entity-field v-if="global.auth.is('admin')" :entity="entity" prop="type" @change="entity.save(true).then(() => global.reload())" classes="col-6 sm:col-12"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="cnpj" label="CNPJ"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="emailPrivado" label="<?= i::__('E-mail privado ') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefonePublico" label="<?= i::__('Telefone público com DDD') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="nomeCompleto" label="<?php i::_e('Razão Social') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone2" label="<?= i::__('Telefone privado 2 com DDD') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-12" prop="nomeSocial" label="<?php i::_e('Nome Fantasia') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.cnpj?.length == 18)" :entity="entity" classes="col-12" prop="cnpjAnexo" title-modal="<?php i::_e('Anexar CNPJ - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnpj" :hide-label="true"></entity-field>
                                    <entity-field :entity="entity" classes="col-12" prop="dataDeNascimento" label="<?= i::__('Data de fundação') ?>"></entity-field>

                                    <div class="col-12 divider"></div>
                                    <country-address-form :entity="entity" class="col-12"></country-address-form>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Informações Públicas"); ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                                </div>
                            </template>
                            <template #body>
                                <div class="grid-12">
                                    <p class="col-12 data-subtitle bold"><?php i::_e("CNH"); ?></p>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhNumero" label="<?= i::__('Número de registro') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhCategoria" label="<?= i::__('Categoria') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhValidade" label="<?= i::__('Validade') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.cnhNumero && entity?.cnhCategoria?.length && entity?.cnhValidade)" :entity="entity" classes="col-12" prop="cnhAnexo" title-modal="<?php i::_e('Anexar CNH - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnh" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <p class="col-12 data-subtitle bold"><?php i::_e("RG"); ?></p>
                                    <entity-field :entity="entity" classes="col-5 sm:col-12" prop="rgNumero" label="<?= i::__('Documento') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-3 sm:col-12" prop="rgOrgaoEmissor" label="<?= i::__('Órgão Emissor') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="rgUF" label="<?= i::__('UF') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.rgNumero && entity?.rgOrgaoEmissor && entity?.rgUF)" :entity="entity" classes="col-12" prop="rgAnexo" title-modal="<?php i::_e('Anexar RG - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-rg" :hide-label="true"></entity-field>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section edit-1__section--social">
                        <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                    </div>
                    </div>
                    </main>
                </mc-container>
                <?php $this->applyTemplateHook('entity-info-validation', 'end') ?>
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
                                    <div class="single-1__people-card edit-2__people-collaborators">
                                        <?php if ($collaborator_count === 0) { ?>
                                            <p class="single-1__administration-empty">
                                                <?php i::_e('Você ainda não adicionou nenhum colaborador na organização. Crie um grupo e edite novos colaboradores.') ?>
                                            </p>
                                        <?php } ?>

                                        <entity-related-agents
                                            :entity="entity"
                                            editable></entity-related-agents>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Administradores') ?>"
                                    :meta="{ count: <?= (int) $admin_count ?> }"
                                    slug="administradores">
                                    <?php if ($admin_count === 0) { ?>
                                        <p class="single-1__administration-empty">
                                            <?php i::_e('Essa pessoa não possui administradores.') ?>
                                        </p>
                                    <?php } else { ?>
                                        <div class="single-1__administration-card">
                                            <h2 class="single-1__administration-title"><?php i::_e('Administradores do perfil'); ?></h2>
                                            <p class="single-1__administration-intro">
                                                <?php i::_e("Administradores do perfil podem visualizar e editar os dados públicos e pessoais do agente cultural que administram, além de fazer inscrições em seu nome nas oportunidades vinculadas na plataforma e transferir,editar e/ou excluir suas entidades. A administração dos perfis só e possivel mediante a autorização do proprietário do perfil."); ?>
                                            </p>
                                            <entity-admins
                                                :entity="entity"
                                                variant="edit"
                                                editable
                                                classes="single-1__administration-admins"></entity-admins>
                                        </div>
                                    <?php } ?>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Proprietário(a)') ?>"
                                    :meta="{ count: <?= (int) $owner_count ?> }"
                                    slug="proprietario">
                                    <div class="single-1__people-card">
                                        <entity-owner
                                            :entity="entity"
                                            title="<?php i::_e('Proprietário(a)') ?>"
                                            classes="col-12"
                                            editable>
                                        </entity-owner>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
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
                                        <p class="edit-1__portfolio-helper"><?php i::_e('Insira arquivos de até <strong>' . $app->getMaxUploadSize() . '</strong>. Os arquivos serão exibidos publicamente e poderão ser baixados por qualquer pessoa.') ?></p>
                                        <entity-files-list
                                            :entity="entity"
                                            classes="portfolio-edit-list"
                                            group="downloads"
                                            title="<?php i::_e('Arquivos') ?>"
                                            editable
                                            hide-title
                                            labeled-actions
                                            button-label="<?php i::esc_attr_e('Adicionar arquivo') ?>"
                                            button-primary>
                                        </entity-files-list>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                                    <div class="edit-1__portfolio-card">
                                        <p class="edit-1__portfolio-helper"><?php i::_e('Os links serão exibidos publicamente e poderão ser acessados por qualquer pessoa.') ?></p>
                                        <entity-links
                                            :entity="entity"
                                            classes="portfolio-edit-list"
                                            title="<?php i::esc_attr_e('Links'); ?>"
                                            editable
                                            hide-title
                                            labeled-actions
                                            button-label="<?php i::esc_attr_e('Adicionar link') ?>"
                                            button-primary>
                                        </entity-links>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                    <div class="edit-1__portfolio-card">
                                        <p class="edit-1__portfolio-helper"><?php i::_e('Faça upload do seu vídeo em alguma plataforma de hospedagem de vídeos e insira na plataforma Mapas através da URL. Os vídeos serão exibidos publicamente e poderão ser acessados por qualquer pessoa.') ?></p>
                                        <entity-gallery-video
                                            :entity="entity"
                                            classes="portfolio-edit-videos"
                                            title="<?php i::esc_attr_e('Vídeos') ?>"
                                            editable
                                            hide-title
                                            labeled-actions
                                            button-label="<?php i::esc_attr_e('Adicionar vídeo') ?>"
                                            button-primary>
                                        </entity-gallery-video>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                    <div class="edit-1__portfolio-card">
                                        <p class="edit-1__portfolio-helper"><?php i::_e('Insira imagens de até <strong>' . $app->getMaxUploadSize() . '</strong>. As imagens serão exibidas publicamente e poderão ser baixadas por qualquer pessoa.') ?></p>
                                        <entity-gallery
                                            :entity="entity"
                                            classes="portfolio-edit-images"
                                            title="<?php i::esc_attr_e('Fotos') ?>"
                                            editable
                                            hide-title
                                            button-label="<?php i::esc_attr_e('Adicionar imagem') ?>"
                                            button-primary>
                                        </entity-gallery>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Documentos') ?>" slug="documentos">
                <mc-container>
                    <main class="edit-1__perfil-main">
                        <div class="edit-1__section">
                            <p class="edit-1__section-subtitle">
                                <?php i::_e("Os documentos registrados no perfil da organização podem ser utilizados no processo de inscrição em oportunidades. Os documentos apenas aparecerão publicamente no seu perfil se marcar a opção 'Mostrar publicamente'.") ?>
                            </p>

                            <div class="grid-12">
                                <div class="col-12 divider"></div>
                                <entity-field :entity="entity" classes="col-12" prop="certidaoFiscalAnexo" label="<?= i::__('Certidão de Regularidade Fiscal') ?>" title-modal="<?php i::_e('Anexar Certidão Fiscal - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-fiscal"></entity-field>
                                <div class="col-12 divider"></div>
                                <entity-field :entity="entity" classes="col-12" prop="certidaoTrabalhistaAnexo" label="<?= i::__('Certidão de Regularidade Trabalhista') ?>" title-modal="<?php i::_e('Anexar Certidão Trabalhista - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-trabalhista"></entity-field>
                                <div class="col-12 divider"></div>
                                <entity-field :entity="entity" classes="col-12" prop="certidaoPrestacaoContasAnexo" label="<?= i::__('Certidão de Prestação de Contas') ?>" title-modal="<?php i::_e('Anexar Certidão de Prestação de Contas - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-contas"></entity-field>
                            </div>
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
