<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    mc-avatar
    mc-entities
    mc-icon
    mc-link
    complaint-suggestion
    entity-actions
    entity-admins
    entity-card
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

$label = $this->isRequestedEntityMine() ? i::__('Meus projetos') : i::__('Projetos');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'single', [$entity->id])],
];

$events = method_exists($entity, 'getEvents') ? $entity->getEvents() : [];
$has_events = is_countable($events) ? count($events) > 0 : false;

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

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                <entity-data class="metadata__id" :entity="entity" prop="id" label="<?php i::_e('ID:') ?>"></entity-data>
            </dl>
            <dl v-if="entity.type">
                <dt><?= i::__('Tipo') ?></dt>
                <dd :class="[entity.__objectType + '__color', 'type']">{{ entity.type.name }}</dd>
            </dl>
            <dl v-if="entity.parent">
                <dt><?= i::__('Projeto integrante de') ?></dt>
                <dd :class="[entity.__objectType + '__color', 'type']">
                    <mc-link :entity="entity.parent">{{ entity.parent.name }}</mc-link>
                </dd>
            </dl>
        </template>
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
            <?php $this->applyTemplateHook('tabs', 'begin') ?>

            <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
                <mc-container>
                    <div v-if="entity.children?.length > 0" class="single-1__collective-agents">
                        <div class="single-1__collective-agents-card">
                            <div class="single-1__collective-agents-header">
                                <h3 class="single-1__collective-agents-title">
                                    <?php i::_e('Subprojetos de'); ?> {{ entity.name }}
                                </h3>
                                <a
                                    v-if="entity.children.length > 0"
                                    class="single-1__collective-agents-see-all"
                                    href="#subprojetos">
                                    <?php i::_e('ver todos'); ?>
                                    <mc-icon name="arrow-right"></mc-icon>
                                </a>
                            </div>
                            <mc-entities
                                type="project"
                                select="id,name,files.avatar,singleUrl"
                                order="name ASC"
                                :ids="entity.children.slice(0, 3).map((item) => item.id ?? item)"
                                #default="{entities}">
                                <ul v-if="entities.length" class="single-1__collective-agents-list">
                                    <li v-for="project in entities" :key="project.id" class="single-1__collective-agents-item">
                                        <mc-link :entity="project" class="single-1__collective-agents-link">
                                            <mc-avatar :entity="project" size="small"></mc-avatar>
                                            <span class="single-1__collective-agents-name">{{ project.name }}</span>
                                        </mc-link>
                                    </li>
                                </ul>
                            </mc-entities>
                        </div>
                    </div>

                    <div class="single-1__presentation-card">
                        <h2 class="single-1__presentation-title"><?php i::_e('Apresentação'); ?></h2>
                        <div class="single-1__presentation-content">
                            <div v-if="entity.terms?.tag?.length" class="single-1__presentation-item">
                                <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'before') ?>
                                <entity-terms
                                    :entity="entity"
                                    hide-required
                                    classes="col-12"
                                    taxonomy="tag"
                                    :title="'<?php i::esc_attr_e('Tags'); ?>' + (entity.terms?.tag?.length ? ' (' + entity.terms.tag.length + ')' : '')">
                                </entity-terms>
                                <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'after') ?>
                            </div>

                            <div v-if="entity.longDescription" class="single-1__presentation-item single-1__description-block">
                                <entity-description-collapse
                                    :text="entity.longDescription"
                                    label="<?php i::esc_attr_e('Descrição'); ?>">
                                </entity-description-collapse>
                            </div>

                            <div v-if="entity.startsOn || entity.endsOn" class="grid-12 single-1__presentation-item">
                                <div v-if="entity.startsOn" class="col-6 sm:col-12">
                                    <entity-data :entity="entity" prop="startsOn" label="<?php i::_e('Data de início') ?>"></entity-data>
                                </div>
                                <div v-if="entity.endsOn" class="col-6 sm:col-12">
                                    <entity-data :entity="entity" prop="endsOn" label="<?php i::_e('Data de fim') ?>"></entity-data>
                                </div>
                            </div>

                            <div class="grid-12 single-1__presentation-item single-1__presentation-contacts">
                                <div class="col-4 sm:col-12">
                                    <entity-data :entity="entity" prop="site" label="<?php i::_e('Site') ?>"></entity-data>
                                </div>
                                <div class="col-4 sm:col-12">
                                    <entity-data :entity="entity" prop="telefonePublico" label="<?php i::_e('Telefone') ?>"></entity-data>
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

                    <template v-if="entity.currentUserPermissions.viewPrivateData">
                        <div
                            v-if="entity.emailPrivado || entity.telefone1 || entity.telefone2"
                            class="single-1__personal-card">
                            <h2 class="single-1__personal-title"><?php i::_e('Dados de contato privado'); ?></h2>
                            <div class="grid-12 single-1__personal-grid">
                                <entity-data v-if="entity.emailPrivado" :entity="entity" classes="col-4 sm:col-12" prop="emailPrivado" label="<?php i::_e('E-mail privado') ?>"></entity-data>
                                <entity-data v-if="entity.telefone1" :entity="entity" classes="col-4 sm:col-12" prop="telefone1" label="<?php i::_e('Telefone privado 1') ?>"></entity-data>
                                <entity-data v-if="entity.telefone2" :entity="entity" classes="col-4 sm:col-12" prop="telefone2" label="<?php i::_e('Telefone privado 2') ?>"></entity-data>
                            </div>
                        </div>
                    </template>

                    <complaint-suggestion :entity="entity" classes="col-12" :show-contact="false"></complaint-suggestion>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Administração e relações') ?>" slug="pessoas">
                <mc-container>
                    <main>
                        <div class="single-1__people">
                            <?php if ($owner_count > 0): ?>
                            <section class="single-1__people-section">
                                <h2 class="single-1__people-section-title">
                                    <?php i::_e('Proprietário'); ?> (<?= (int) $owner_count ?>)
                                </h2>
                                <div class="single-1__people-card">
                                    <entity-connections-list
                                        type="agent"
                                        :ids="entity.owner ? [entity.owner.id ?? entity.owner] : []"
                                        role-label="<?php i::esc_attr_e('Proprietário(a)') ?>">
                                    </entity-connections-list>
                                </div>
                            </section>
                            <?php endif; ?>

                            <?php if ($admin_count > 0): ?>
                            <section class="single-1__people-section">
                                <h2 class="single-1__people-section-title">
                                    <?php i::_e('Administradores'); ?> (<?= (int) $admin_count ?>)
                                </h2>

                                <div class="single-1__administration-card">
                                    <p class="single-1__administration-intro"><?php i::_e('Administradores do perfil podem visualizar e editar os dados públicos do projeto que administram, além de transferir, editar e/ou excluir a entidade. A administração dos perfis só é possível mediante a autorização do proprietário do perfil.'); ?></p>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'before') ?>
                                    <entity-admins :entity="entity" variant="list" classes="single-1__administration-admins"></entity-admins>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'after') ?>
                                </div>
                            </section>
                            <?php endif; ?>

                            <?php if ($collaborator_count > 0): ?>
                            <section class="single-1__people-section">
                                <h2 class="single-1__people-section-title">
                                    <?php i::_e('Colaboradores'); ?> (<?= (int) $collaborator_count ?>)
                                </h2>
                                <div class="single-1__people-collaborators">
                                    <entity-people-collaborators :entity="entity"></entity-people-collaborators>
                                </div>
                            </section>
                            <?php endif; ?>
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
                                <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery :entity="entity" hide-title></entity-gallery>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery-video :entity="entity" hide-title></entity-gallery-video>
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
                        <?php $this->applyTemplateHook('single1-entity-seals', 'before') ?>
                        <div class="single-1__seals">
                            <entity-seals-list
                                :entity="entity"
                                :editable="!!entity.currentUserPermissions?.createSealRelation"
                                classes="single-1__seals-list"
                                empty-message="<?php i::esc_attr_e('Esse projeto não possui selos.') ?>">
                            </entity-seals-list>
                        </div>
                        <?php $this->applyTemplateHook('single1-entity-seals', 'after') ?>
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
                            <mc-tabs class="tabs" sync-hash default-tab="subprojetos">
                                <template #header="{ tab }">
                                    <span>{{ tab.label }}</span>
                                    <span v-if="tab.meta?.count > 0" class="single-1__connections-count">
                                        {{ tab.meta.count }}
                                    </span>
                                </template>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Subprojetos') ?>"
                                    :meta="{ count: entity.children?.length || 0 }"
                                    slug="subprojetos">
                                    <div class="single-1__connections-card">
                                        <entity-connections-list
                                            type="project"
                                            :ids="entity.children || []"
                                            empty-message="<?php i::esc_attr_e('Esse projeto não possui subprojetos.') ?>">
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
                                            empty-message="<?php i::esc_attr_e('Esse projeto não possui oportunidades.') ?>">
                                        </entity-connections-list>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>

            <?php if ($has_events) { ?>
            <mc-tab icon="event" label="<?= i::_e('Eventos') ?>" slug="eventos">
                <mc-container>
                    <main class="grid-12">
                        <mc-entities
                            type="event"
                            select="name,shortDescription,files.avatar,seals,terms,occurrences,project,status,singleUrl"
                            :query="{project: `EQ(${entity.id})`, status: 'EQ(1)'}"
                            :limit="20"
                            watch-query>
                            <template #default="{entities}">
                                <entity-card :entity="event" v-for="event in entities" :key="event.__objectId" class="col-12">
                                    <template #avatar>
                                        <mc-avatar :entity="event" size="medium"></mc-avatar>
                                    </template>
                                    <template #type>
                                        <span>
                                            <?= i::__('EVENTO') ?>
                                            <span class="event__status">{{ event.status == 1 ? '<?= i::__('Ativo') ?>' : '<?= i::__('Inativo') ?>' }}</span>
                                        </span>
                                    </template>
                                </entity-card>
                            </template>
                        </mc-entities>
                    </main>
                </mc-container>
            </mc-tab>
            <?php } ?>

            <?php $this->applyTemplateHook('tabs', 'end') ?>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>
