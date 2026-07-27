<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    agent-data-2
    entity-map
    mc-avatar
    mc-entities
    mc-icon
    mc-link
    complaint-suggestion
    entity-actions
    entity-admins
    entity-connections-list
    entity-data
    entity-description-collapse
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-people-collaborators
    entity-seals-list
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-share-links
    mc-modal
    mc-tab
    mc-tabs
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];

// Conta só o que a listagem consegue exibir: pendentes ficam ocultos para quem
// não tem permissão de ver dados privados / gerir relações (igual à ApiQuery).
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

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #actions>
            <mc-modal title="<?= i::__('Compartilhar') ?>" classes="entity-header__share-modal">
                <template #default>
                    <mc-share-links classes="col-12" title="" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                    <div class="entity-header__share-copy">
                        <p><?php i::_e('Ou copie o link'); ?></p>
                        <button type="button" class="button button--primary-outline button--icon" onclick="navigator.clipboard.writeText(window.location.href)">
                            <mc-icon name="link"></mc-icon>
                            <?php i::_e('Copiar link'); ?>
                        </button>
                    </div>
                </template>
                <template #button="modal">
                    <button type="button" class="button button--primary-outline button--icon" @click="modal.open()">
                        <mc-icon name="share"></mc-icon>
                        <?php i::_e('Compartilhar'); ?>
                    </button>
                </template>
            </mc-modal>
            <complaint-suggestion
                :entity="entity"
                :show-complaint="false"
                contact-button-label="<?php i::esc_attr_e('Enviar mensagem') ?>"
                contact-button-classes="button button--primary button--icon">
            </complaint-suggestion>
        </template>
    </entity-header>
    <div class="single-1__main-tabs">
        <mc-tabs class="tabs" sync-hash>
            <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
                <mc-container>
                    <main class="single-1__perfil-main">
                        <entity-people-collaborators
                            :entity="entity"
                            preview
                            classes="single-1__people-preview-wrap">
                        </entity-people-collaborators>

                        <div class="single-1__inner-tabs">
                        <mc-tabs class="tabs" sync-hash>
                            <mc-tab label="<?= i::_e('Público') ?>" slug="publico">
                                <div class="single-1__presentation-card">
                                    <h2 class="single-1__presentation-title"><?php i::_e('Apresentação'); ?></h2>
                                    <div class="single-1__presentation-content">

                                        <div v-if="entity.terms?.area?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                classes="col-12"
                                                taxonomy="area"
                                                :title="'<?php i::esc_attr_e('Área(s) de atuação'); ?>' + (entity.terms?.area?.length ? ' (' + entity.terms.area.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area', 'after') ?>
                                        </div>

                                        <div v-if="entity.terms?.funcao?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-funcao', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                taxonomy="funcao"
                                                classes="col-12"
                                                :title="'<?php i::esc_attr_e('Função(ões) na cultura'); ?>' + (entity.terms?.funcao?.length ? ' (' + entity.terms.funcao.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-funcao', 'after') ?>
                                        </div>

                                        <div v-if="entity.terms?.tag?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                classes="col-12"
                                                taxonomy="tag"
                                                :title="'<?php i::esc_attr_e('Tags'); ?>' + (entity.terms?.tag?.length ? ' (' + entity.terms.tag.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag', 'after') ?>
                                        </div>

                                        <div v-if="entity.longDescription" class="single-1__presentation-item single-1__description-block">
                                            <entity-description-collapse
                                                :text="entity.longDescription"
                                                label="<?php i::esc_attr_e('Descrição'); ?>">
                                            </entity-description-collapse>
                                        </div>

                                        <div class="grid-12 single-1__presentation-item single-1__presentation-contacts">
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
                                            <?php $this->applyTemplateHook('single2-entity-info-social-media', 'before') ?>
                                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                                            <?php $this->applyTemplateHook('single2-entity-info-social-media', 'after') ?>
                                        </template>
                                    </mc-card>
                                </div>
                                <complaint-suggestion :entity="entity" classes="col-12" :show-contact="false"></complaint-suggestion>

                            </mc-tab>

                            <mc-tab label="<?= i::_e('Dados organizacionais') ?>" slug="dados-organizacionais">
                                <div class="single-1__personal-card">
                                    <h2 class="single-1__personal-title"><?php i::_e('Dados organizacionais'); ?></h2>
                                    <agent-data-2 :entity="entity"></agent-data-2>
                                </div>

                                <?php $this->applyTemplateHook('single2-agent-documents', 'before') ?>
                                <div
                                    v-if="entity.currentUserPermissions.viewPrivateData && (entity.files?.['docs-certidao-fiscal'] || entity.files?.['docs-certidao-trabalhista'] || entity.files?.['docs-certidao-contas'] || entity.files?.['docs-cnpj'] || entity.files?.downloads)"
                                    class="single-1__personal-card single-1__documents-card">
                                    <h2 class="single-1__personal-title"><?php i::_e('Documentos e Certidões'); ?></h2>
                                    <div class="single-1__documents-list">
                                        <entity-files-list v-if="entity.files?.['docs-cnpj']" :entity="entity" classes="docs-anexo-list" group="docs-cnpj" seal-prop="cnpjAnexo" title="<?php i::_e('Comprovante de CNPJ'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                        <entity-files-list v-if="entity.files?.['docs-certidao-fiscal']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-fiscal" seal-prop="certidaoFiscalAnexo" title="<?php i::_e('Certidão de Regularidade Fiscal'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                        <entity-files-list v-if="entity.files?.['docs-certidao-trabalhista']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-trabalhista" seal-prop="certidaoTrabalhistaAnexo" title="<?php i::_e('Certidão de Regularidade Trabalhista'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                        <entity-files-list v-if="entity.files?.['docs-certidao-contas']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-contas" seal-prop="certidaoPrestacaoContasAnexo" title="<?php i::_e('Certidão de Prestação de Contas'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                        <entity-files-list v-if="entity.files?.downloads" :entity="entity" group="downloads" title="<?php i::_e('Outros documentos'); ?>" hide-title view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                    </div>
                                </div>
                                <?php $this->applyTemplateHook('single2-agent-documents', 'after') ?>
                            </mc-tab>

                            <mc-tab label="<?= i::_e('Endereço') ?>" slug="endereco">
                                <p
                                    v-if="!entity.publicLocation || !(
                                        entity.En_CEP ||
                                        entity.En_Nome_Logradouro ||
                                        entity.En_Num ||
                                        entity.En_Bairro ||
                                        entity.En_Municipio ||
                                        entity.En_Estado ||
                                        entity.address_postalCode ||
                                        entity.address_line1 ||
                                        ((entity.location?.lat ?? entity.location?.latitude) && (entity.location?.lng ?? entity.location?.longitude))
                                    )"
                                    class="single-1__address-empty">
                                    <?php i::_e('Essa pessoa não compartilhou dados de endereço.'); ?>
                                </p>

                                <div v-else class="single-1__address-card">
                                    <div class="single-1__address-header">
                                        <h2 class="single-1__address-title"><?php i::_e('Dados do endereço'); ?></h2>
                                        <a
                                            v-if="(entity.location?.lat ?? entity.location?.latitude) && (entity.location?.lng ?? entity.location?.longitude)"
                                            class="single-1__address-map-link"
                                            href="#localizacao">
                                            <?php i::_e('ver mapa'); ?>
                                        </a>
                                    </div>

                                    <!-- Endereço brasileiro (En_*) -->
                                    <div
                                        v-if="(entity.address_level0 || entity.En_Pais || 'BR') === 'BR'"
                                        class="grid-12 single-1__address-grid">
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_CEP" label="<?php i::_e('CEP') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Nome_Logradouro" label="<?php i::_e('Logradouro') ?>"></entity-data>
                                        </div>
                                        <div class="col-2 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Num" label="<?php i::_e('Número') ?>"></entity-data>
                                        </div>
                                        <div class="col-2 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Bairro" label="<?php i::_e('Bairro') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Complemento" label="<?php i::_e('Complemento (ou ponto de referência)') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Estado" label="<?php i::_e('Estado') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Municipio" label="<?php i::_e('Município') ?>"></entity-data>
                                        </div>
                                    </div>

                                    <!-- Endereço internacional (address_*) -->
                                    <div v-else class="grid-12 single-1__address-grid">
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_postalCode" label="<?php i::_e('Código postal') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level0" label="<?php i::_e('País') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="address_line1" label="<?php i::_e('Endereço') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_line2" label="<?php i::_e('Complemento') ?>"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level1" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level1"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level2" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level2"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level3" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level3"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level4" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level4"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level5" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level5"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level6" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level6"></entity-data>
                                        </div>
                                    </div>

                                    <div id="localizacao" class="single-1__address-location">
                                        <h3 class="single-1__address-subtitle"><?php i::_e('Localização'); ?></h3>
                                        <div
                                            v-if="(entity.location?.lat ?? entity.location?.latitude) && (entity.location?.lng ?? entity.location?.longitude)"
                                            class="single-1__address-map">
                                            <entity-map :entity="entity"></entity-map>
                                        </div>
                                        <div v-else class="single-1__address-map-empty">
                                            <p><?php i::_e('Localização não informada'); ?></p>
                                        </div>
                                    </div>
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
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui colaboradores.') ?>">
                                        </entity-people-collaborators>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Administradores') ?>"
                                    :meta="{ count: <?= (int) $admin_count ?> }"
                                    slug="administradores">
                                    <p
                                        v-if="!entity.agentRelations?.['group-admin']?.length"
                                        class="single-1__administration-empty">
                                        <?php i::_e('Essa pessoa não possui administradores.'); ?>
                                    </p>

                                    <div v-else class="single-1__administration-card">
                                        <h2 class="single-1__administration-title"><?php i::_e('Administradores do perfil'); ?></h2>
                                        <p class="single-1__administration-intro"><?php i::_e("Administradores do perfil podem visualizar e editar os dados públicos e pessoais do agente cultural que administram, além de fazer inscrições em seu nome nas oportunidades vinculadas na plataforma e transferir,editar e/ou excluir suas entidades. A administração dos perfis só e possivel mediante a autorização do proprietário do perfil."); ?></p>
                                        <?php $this->applyTemplateHook('single2-entity-info-entity-admins', 'before') ?>
                                        <entity-admins :entity="entity" variant="list" classes="single-1__administration-admins"></entity-admins>
                                        <?php $this->applyTemplateHook('single2-entity-info-entity-admins', 'after') ?>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Proprietário') ?>"
                                    :meta="{ count: <?= (int) $owner_count ?> }"
                                    slug="proprietario">
                                    <div class="single-1__people-card">
                                        <entity-connections-list
                                            type="agent"
                                            :ids="entity.parent ? [entity.parent.id ?? entity.parent] : []"
                                            role-label="<?php i::esc_attr_e('Proprietário(a)') ?>"
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui proprietário.') ?>">
                                        </entity-connections-list>
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
                        <div class="single-1__portfolio single-1__inner-tabs">
                            <mc-tabs class="tabs" sync-hash>
                                <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                                    <div class="single-1__portfolio-card">
                                        <template v-if="entity.currentUserPermissions.viewPrivateData && (entity.files?.['docs-curriculo'] || entity.files?.['docs-portfolio'])">
                                            <entity-files-list v-if="entity.files?.['docs-curriculo']" :entity="entity" classes="portfolio-files-list" group="docs-curriculo" seal-prop="curriculoAnexo" title="<?php i::_e('Currículo'); ?>" view-action></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-portfolio']" :entity="entity" classes="portfolio-files-list" group="docs-portfolio" seal-prop="portfolioAnexo" title="<?php i::_e('Portfólio'); ?>" view-action></entity-files-list>
                                        </template>
                                        <entity-files-list
                                            v-if="entity.files?.downloads"
                                            :entity="entity"
                                            classes="portfolio-files-list"
                                            group="downloads"
                                            title="<?php i::esc_attr_e('Arquivos para download'); ?>"
                                            hide-title
                                            view-action>
                                        </entity-files-list>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                                    <div class="single-1__portfolio-card">
                                        <entity-links :entity="entity" title="<?php i::_e('Links'); ?>" hide-title></entity-links>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery-video :entity="entity" hide-title></entity-gallery-video>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery :entity="entity" hide-title></entity-gallery>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Selos') ?>" slug="selos">
                <mc-container>
                    <main>
                        <?php $this->applyTemplateHook('single2-entity-info-entity-seals', 'before') ?>
                        <div class="single-1__seals">
                            <entity-seals-list
                                :entity="entity"
                                :editable="!!entity.currentUserPermissions?.createSealRelation"
                                classes="single-1__seals-list"
                                empty-message="<?php i::esc_attr_e('Essa pessoa não possui selos.') ?>">
                            </entity-seals-list>
                        </div>
                        <?php $this->applyTemplateHook('single2-entity-info-entity-seals', 'after') ?>
                    </main>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Conexões') ?>" slug="conexoes">
                <mc-container>
                    <main>
                        <?php
                        $opportunity_count = is_array($this->jsObject['opportunityList']['opportunity'] ?? null)
                            ? count($this->jsObject['opportunityList']['opportunity'])
                            : 0;
                        ?>
                        <div class="single-1__connections single-1__inner-tabs">
                            <mc-tabs class="tabs" sync-hash default-tab="organizacoes">
                                <template #header="{ tab }">
                                    <span>{{ tab.label }}</span>
                                    <span v-if="tab.meta?.count > 0" class="single-1__connections-count">
                                        {{ tab.meta.count }}
                                    </span>
                                </template>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Organizações') ?>"
                                    :meta="{ count: entity.children?.length || 0 }"
                                    slug="organizacoes">
                                    <div class="single-1__connections-card">
                                        <entity-connections-list
                                            type="agent"
                                            :ids="entity.children || []"
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui organizações.') ?>">
                                        </entity-connections-list>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Projetos') ?>"
                                    :meta="{ count: entity.projects?.length || 0 }"
                                    slug="projetos">
                                    <div class="single-1__connections-card">
                                        <entity-connections-list
                                            type="project"
                                            :ids="entity.projects || []"
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui projetos.') ?>">
                                        </entity-connections-list>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Espaços') ?>"
                                    :meta="{ count: entity.spaces?.length || 0 }"
                                    slug="espacos">
                                    <div class="single-1__connections-card">
                                        <entity-connections-list
                                            type="space"
                                            :ids="entity.spaces || []"
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui espaços.') ?>">
                                        </entity-connections-list>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Oportunidades') ?>"
                                    :meta="{ count: <?= (int) $opportunity_count ?> }"
                                    slug="oportunidades">
                                    <div class="single-1__connections-card">
                                        <entity-connections-list
                                            type="opportunity"
                                            empty-message="<?php i::esc_attr_e('Essa pessoa não possui oportunidades.') ?>">
                                        </entity-connections-list>
                                    </div>
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
