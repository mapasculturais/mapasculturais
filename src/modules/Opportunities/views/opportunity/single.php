<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\Entities\Opportunity $entity
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->addOpportunityPhasesToJs($entity);
$this->useOpportunityAPI();

$this->import('
    complaint-suggestion
    entity-admins
    entity-actions
    entity-connections-list
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-people-collaborators
    entity-seals-list
    entity-social-media
    entity-terms
    evaluations-list
    mc-breadcrumb
    mc-card
    mc-container
    mc-icon
    mc-link
    mc-modal
    mc-share-links
    mc-tab
    mc-tabs
    opportunity-evaluations-tab
    opportunity-phase-evaluation
    opportunity-phases-timeline
    opportunity-subscription
    opportunity-subscription-list
    v1-embed-tool
');

$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('search', 'opportunities')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
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
$owner_count = $entity->owner ? 1 : 0;
?>
<div class="main-app single-1 single-opportunity">
  <mc-breadcrumb></mc-breadcrumb>
  <entity-header :entity="entity">
    <template #metadata>
        <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
            <dt><?= i::__('ID') ?></dt>
            <dd><strong>{{ entity.id }}</strong></dd>
        </dl>
        <dl v-if="entity.type">
            <dt><?= i::__('Tipo') ?></dt>
            <dd :class="[entity.__objectType+'__color', 'type']">{{ entity.type.name }}</dd>
        </dl>
        <dl v-if="entity.ownerEntity" class="single-opportunity__owner">
            <dt v-if="entity.ownerEntity.__objectType === 'agent'"><?= i::__('Conexão com o agente') ?></dt>
            <dt v-else-if="entity.ownerEntity.__objectType === 'project'"><?= i::__('Conexão com o projeto') ?></dt>
            <dt v-else-if="entity.ownerEntity.__objectType === 'event'"><?= i::__('Conexão com o evento') ?></dt>
            <dt v-else-if="entity.ownerEntity.__objectType === 'space'"><?= i::__('Conexão com o espaço') ?></dt>
            <dt v-else><?= i::__('Conexão') ?></dt>
            <dd>
                <mc-link :entity="entity.ownerEntity"></mc-link>
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
            <?php $this->applyTemplateHook("tabs", "begin") ?>

            <mc-tab label="<?= i::__('Informações') ?>" slug="info">
                <mc-container>
                    <div class="single-1__presentation-card single-opportunity__registration-section">
                        <div class="single-opportunity__subscription-row">
                            <div class="single-opportunity__subscription-main">
                                <opportunity-subscription :entity="entity"></opportunity-subscription>
                                <opportunity-subscription-list classes="single-opportunity__subscriptions-list"></opportunity-subscription-list>
                            </div>
                            <aside class="single-opportunity__timeline">
                                <opportunity-phases-timeline :entity-status="entity.status"></opportunity-phases-timeline>

                                <div
                                    v-if="entity.terms?.area?.length || entity.terms?.tag?.length"
                                    class="single-opportunity__timeline-terms">
                                    <div v-if="entity.terms?.area?.length" class="single-opportunity__timeline-terms-item">
                                        <entity-terms
                                            :entity="entity"
                                            hide-required
                                            classes="col-12"
                                            taxonomy="area"
                                            title="<?php i::esc_attr_e('Área de Interesse'); ?>">
                                        </entity-terms>
                                    </div>

                                    <div v-if="entity.terms?.tag?.length" class="single-opportunity__timeline-terms-item">
                                        <entity-terms
                                            :entity="entity"
                                            hide-required
                                            classes="col-12"
                                            taxonomy="tag"
                                            :title="'<?php i::esc_attr_e('Tags'); ?>' + (entity.terms?.tag?.length ? ' (' + entity.terms.tag.length + ')' : '')">
                                        </entity-terms>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <div
                        v-if="entity.longDescription"
                        class="single-1__presentation-card">
                        <h2 class="single-1__presentation-title"><?php i::_e('Sobre'); ?></h2>
                        <div class="single-1__presentation-content">
                            <div class="single-1__presentation-item single-1__description-block">
                                <div class="entity-description-collapse">
                                    <div class="entity-description-collapse__body">
                                        <p class="entity-description-collapse__text" v-html="entity.longDescription"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="entity.instagram || entity.twitter || entity.vimeo || entity.linkedin || entity.facebook || entity.youtube || entity.spotify || entity.pinterest || entity.tiktok || entity.fediverso"
                        class="col-12 single-1__social-media">
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
                                            empty-message="<?php i::esc_attr_e('Essa oportunidade não possui colaboradores.') ?>">
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
                                        <?php i::_e('Essa oportunidade não possui administradores.'); ?>
                                    </p>

                                    <div v-else class="single-1__administration-card">
                                        <h2 class="single-1__administration-title"><?php i::_e('Administradores do perfil'); ?></h2>
                                        <p class="single-1__administration-intro"><?php i::_e('Administradores do perfil podem visualizar e editar os dados públicos da oportunidade que administram, além de transferir, editar e/ou excluir a entidade. A administração dos perfis só é possível mediante a autorização do proprietário do perfil.'); ?></p>
                                        <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'before') ?>
                                        <entity-admins :entity="entity" variant="list" classes="single-1__administration-admins"></entity-admins>
                                        <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'after') ?>
                                    </div>
                                </mc-tab>

                                <mc-tab
                                    label="<?= i::esc_attr_e('Proprietário') ?>"
                                    :meta="{ count: <?= (int) $owner_count ?> }"
                                    slug="proprietario">
                                    <div class="single-1__people-card">
                                        <entity-connections-list
                                            type="agent"
                                            :ids="entity.owner ? [entity.owner.id ?? entity.owner] : []"
                                            role-label="<?php i::esc_attr_e('Proprietário(a)') ?>"
                                            empty-message="<?php i::esc_attr_e('Essa oportunidade não possui proprietário.') ?>">
                                        </entity-connections-list>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Anexos') ?>" slug="anexos">
                <mc-container>
                    <main>
                        <div class="single-1__portfolio single-1__inner-tabs">
                            <mc-tabs class="tabs" sync-hash>
                                <mc-tab label="<?= i::esc_attr_e('Arquivos e Regulamento') ?>" slug="arquivos">
                                    <div
                                        v-if="entity.files?.rules"
                                        class="single-1__portfolio-card single-1__portfolio-section">
                                        <h2 class="single-1__portfolio-section-title"><?php i::_e('Regulamento'); ?></h2>
                                        <entity-files-list
                                            :entity="entity"
                                            classes="portfolio-files-list"
                                            group="rules"
                                            title="<?php i::esc_attr_e('Regulamento'); ?>"
                                            hide-title>
                                        </entity-files-list>
                                    </div>

                                    <div
                                        v-if="entity.files?.downloads"
                                        class="single-1__portfolio-card single-1__portfolio-section">
                                        <h2 class="single-1__portfolio-section-title"><?php i::_e('Arquivos'); ?></h2>
                                        <entity-files-list
                                            :entity="entity"
                                            classes="portfolio-files-list"
                                            group="downloads"
                                            title="<?php i::esc_attr_e('Arquivos para download'); ?>"
                                            hide-title>
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
                                empty-message="<?php i::esc_attr_e('Essa oportunidade não possui selos.') ?>">
                            </entity-seals-list>
                        </div>
                        <?php $this->applyTemplateHook('single1-entity-seals', 'after') ?>
                    </main>
                </mc-container>
            </mc-tab>

            <opportunity-evaluations-tab :entity="entity"></opportunity-evaluations-tab>

            <?php $this->part('opportunity-tab-results', ['entity' => $entity]); ?>

            <?php $this->part('opportunity-tab-support', ['entity' => $entity]); ?>
            <?php $this->applyTemplateHook("tabs", "end") ?>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>
