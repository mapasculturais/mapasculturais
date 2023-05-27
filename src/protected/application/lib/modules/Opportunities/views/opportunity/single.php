<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->addOpportunityPhasesToJs();
$this->useOpportunityAPI();

$this->import('
    complaint-suggestion
    entity-actions
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    evaluations-list
    mc-breadcrumb
    mc-container
    mc-share-links
    mc-tabs
    opportunity-phase-evaluation
    opportunity-phases-timeline
    opportunity-rules
    opportunity-subscription
    opportunity-subscription-list
    v1-embed-tool
');

$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>
<div class="main-app single">
  <mc-breadcrumb></mc-breadcrumb>
  <entity-header :entity="entity"></entity-header>

    <mc-tabs class="tabs">
        <tab label="<?= i::__('Informações') ?>" slug="info">
            <mc-container class="opportunity">
                <main class="grid-12">
                    <opportunity-subscription class="col-12" :entity="entity"></opportunity-subscription>
                    <opportunity-subscription-list class="col-12"></opportunity-subscription-list>
                </main>
                <aside>
                    <div class="grid-12">
                        <opportunity-phases-timeline class="col-12"></opportunity-phases-timeline>
                        <div v-if="entity.files.rules" class="col-12">
                            <a :href="entity.files.rules.url" class="button button--primary-outline" target="_blank"><?= i::__("Baixar regulamento") ?></a>
                        </div>
                    </div>
                </aside>
            </mc-container>

            <mc-container>
                <main>
                    <div class="grid-12">
                        <div class="col-12">
                            <h3><?= i::__("Apresentação") ?></h3>
                            <p v-html="entity.shortDescription"></p>
                        </div>
                        <opportunity-rules :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Regulamento'); ?>"></opportunity-rules>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Links'); ?>"></entity-links>
                        <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <entity-terms :entity="entity" taxonomy="area" classes="col-12" title="<?php i::esc_attr_e('Áreas de interesse'); ?>"></entity-terms>
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags')?>"></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                        <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <mc-share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></mc-share-links>
                    </div>  
                </aside>
                <aside>
                    <div class="grid-12">
                        <complaint-suggestion :entity="entity"></complaint-suggestion>
                    </div>
                </aside>
            </mc-container>
        </tab>

        <tab label="<?= i::__('Avaliações') ?>" slug="evaluations" v-if="entity.currentUserPermissions.evaluateRegistrations">
            <div class="opportunity-container">
                <opportunity-phase-evaluation></opportunity-phase-evaluation>
            </div>
        </tab>

        <?php $this->part('opportunity-tab-results.php', ['entity' => $entity]); ?>
        
        <?php $this->part('opportunity-tab-support.php', ['entity' => $entity]); ?>

    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>
