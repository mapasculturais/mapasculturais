<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
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
    entity-occurrence-list
    entity-people-collaborators
    entity-seals-list
    entity-social-media
    entity-terms
    event-age-rating
    event-info
    mc-breadcrumb
    mc-card
    mc-container
    mc-icon
    mc-link
    mc-modal
    mc-share-links
    opportunity-list
    mc-tab
    mc-tabs
');

$label = $this->isRequestedEntityMine() ? i::__('Meus eventos') : i::__('Eventos');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'events')],
    ['label' => $entity->name, 'url' => $app->createUrl('event', 'single', [$entity->id])],
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

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                <entity-data class="metadata__id" :entity="entity" prop="id" label="<?php i::_e('ID:') ?>"></entity-data>
            </dl>
            <dl v-if="entity.subTitle">
                <dt><?= i::__('Subtítulo') ?></dt>
                <dd>{{ entity.subTitle }}</dd>
            </dl>
            <dl v-if="entity.project">
                <dt><?= i::__('Projeto') ?></dt>
                <dd :class="[entity.__objectType + '__color', 'type']">
                    <mc-link :entity="entity.project">{{ entity.project.name }}</mc-link>
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
                    <opportunity-list></opportunity-list>

                    <div class="single-1__presentation-card">
                        <h2 class="single-1__presentation-title"><?php i::_e('Apresentação'); ?></h2>
                        <div class="single-1__presentation-content">
                            <div v-if="entity.terms?.linguagem?.length" class="single-1__presentation-item">
                                <entity-terms
                                    :entity="entity"
                                    hide-required
                                    classes="col-12"
                                    taxonomy="linguagem"
                                    :title="'<?php i::esc_attr_e('Linguagens culturais'); ?>' + (entity.terms?.linguagem?.length ? ' (' + entity.terms.linguagem.length + ')' : '')">
                                </entity-terms>
                            </div>

                            <div v-if="entity.terms?.tag?.length" class="single-1__presentation-item">
                                <entity-terms
                                    :entity="entity"
                                    hide-required
                                    classes="col-12"
                                    taxonomy="tag"
                                    :title="'<?php i::esc_attr_e('Tags'); ?>' + (entity.terms?.tag?.length ? ' (' + entity.terms.tag.length + ')' : '')">
                                </entity-terms>
                            </div>

                            <div v-if="entity.longDescription" class="single-1__presentation-item single-1__description-block">
                                <entity-description-collapse
                                    :text="entity.longDescription"
                                    label="<?php i::esc_attr_e('Descrição'); ?>">
                                </entity-description-collapse>
                            </div>

                            <div class="grid-12 single-1__presentation-item">
                                <div v-if="entity.classificacaoEtaria" class="col-4 sm:col-12">
                                    <event-age-rating :event="entity"></event-age-rating>
                                </div>
                                <div v-if="entity.event_attendance" class="col-4 sm:col-12">
                                    <entity-data :entity="entity" prop="event_attendance" label="<?php i::_e('Capacidade máxima de pessoas') ?>"></entity-data>
                                </div>
                                <div v-if="entity.telefonePublico" class="col-4 sm:col-12">
                                    <entity-data :entity="entity" prop="telefonePublico" label="<?php i::_e('Telefone para informações') ?>"></entity-data>
                                </div>
                            </div>

                            <div v-if="entity.registrationInfo" class="single-1__presentation-item">
                                <entity-data :entity="entity" prop="registrationInfo" label="<?php i::_e('Informações sobre a inscrição') ?>"></entity-data>
                            </div>

                            <div class="grid-12 single-1__presentation-item single-1__presentation-contacts">
                                <div class="col-4 sm:col-12">
                                    <entity-data :entity="entity" prop="site" label="<?php i::_e('Site') ?>"></entity-data>
                                </div>
                            </div>

                            <event-info :entity="entity" classes="single-1__presentation-item"></event-info>
                        </div>
                    </div>

                    <div class="single-1__presentation-card">
                        <h2 class="single-1__presentation-title"><?php i::_e('Data, hora e local'); ?></h2>
                        <entity-occurrence-list :entity="entity"></entity-occurrence-list>
                    </div>

                    <div class="col-12 single-1__social-media">
                        <mc-card>
                            <template #content>
                                <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            </template>
                        </mc-card>
                    </div>

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
                                    <p class="single-1__administration-intro"><?php i::_e('Administradores do perfil podem visualizar e editar os dados públicos do evento que administram, além de transferir, editar e/ou excluir a entidade. A administração dos perfis só é possível mediante a autorização do proprietário do perfil.'); ?></p>
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

            <mc-tab label="<?= i::esc_attr_e('Evidências') ?>" slug="evidencias">
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
                        <?php $this->applyTemplateHook('single1-entity-seals', 'before') ?>
                        <div class="single-1__seals">
                            <entity-seals-list
                                :entity="entity"
                                :editable="!!entity.currentUserPermissions?.createSealRelation"
                                classes="single-1__seals-list"
                                empty-message="<?php i::esc_attr_e('Esse evento não possui selos.') ?>">
                            </entity-seals-list>
                        </div>
                        <?php $this->applyTemplateHook('single1-entity-seals', 'after') ?>
                    </main>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Projetos') ?>" slug="projetos">
                <mc-container>
                    <main>
                        <div class="single-1__connections-card">
                            <entity-connections-list
                                type="project"
                                :ids="entity.project ? [entity.project] : []"
                                empty-message="<?php i::esc_attr_e('Este evento não está vinculado a um projeto.') ?>">
                            </entity-connections-list>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>

            <?php $this->applyTemplateHook('tabs', 'end') ?>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>
