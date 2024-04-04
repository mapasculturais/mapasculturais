<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\Entities\Opportunity $entity
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->addOpportunityPhasesToJs();
$this->useOpportunityAPI();

$this->import('
    complaint-suggestion
    entity-admins
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
    mc-tab
    mc-tabs
    opportunity-evaluations-tab
    opportunity-phase-evaluation
    opportunity-phases-timeline
    opportunity-rules
    opportunity-subscription
    opportunity-subscription-list
    opportunity-owner-type
    v1-embed-tool
');

$label = $this->isRequestedEntityMine() ? i::__('Minhas oportunidades') : i::__('Oportunidades');

$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => $label, 'url' => $app->createUrl('panel', 'opportunity')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>
<div class="main-app single single-opportunity">
  <mc-breadcrumb></mc-breadcrumb>
  <entity-header :entity="entity">
    <template #metadata>
        <dl v-if="global.showIds[entity.__objectType]" class="metadata__id" v-if="entity.id">
            <dt class="metadata__id--id"><?= i::__('ID') ?></dt>
            <dd><strong>{{entity.id}}</strong></dd>
        </dl> 
        <dl v-if="entity.type">
            <dt><?= i::__('Tipo')?></dt>
            <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.type.name}} </dd>
        </dl>
        <dl v-if="entity.ownerEntity" class="single-opportunity__owner">
            <dt><?= i::__('Vinculado com ') ?><opportunity-owner-type :entity="entity"></opportunity-owner-type></dt>
            <dd><mc-link :entity="entity.ownerEntity"></mc-link></dd>
        </dl>
    </template>
  </entity-header>

    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook("tabs", "begin")?>
        <mc-tab label="<?= i::__('Informações') ?>" slug="info">
            <mc-container class="opportunity">
                <main class="grid-12">
                    <opportunity-subscription class="col-12" :entity="entity"></opportunity-subscription>
                    <opportunity-subscription-list class="col-12"></opportunity-subscription-list>
                    <div class="grid-12">
                        <div class="col-12">
                            <h3><?= i::__("Apresentação") ?></h3>
                            <p v-html="entity.shortDescription"></p>
                        </div>
                        <opportunity-rules :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Regulamento'); ?>"></opportunity-rules>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                        <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <opportunity-phases-timeline class="col-12"></opportunity-phases-timeline>
                        <div v-if="entity.files.rules" class="col-12">
                            <a :href="entity.files.rules.url" class="button button--primary-outline" target="_blank"><?= i::__("Baixar regulamento") ?></a>
                        </div>
                    </div>
                    <div class="flex-container">
                        <entity-terms :entity="entity" hide-required title="<?php i::_e('Área de Interesse') ?>" taxonomy="area"></entity-terms>
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags');?>"></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                        <entity-admins :entity="entity" classes="col-12"></entity-admins>
                        <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <mc-share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></mc-share-links>
                    </div>  
                </aside>
            </mc-container>

            <mc-container>
                <aside>
                    <div class="grid-12">
                        <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>
                    </div>
                </aside>
            </mc-container>
        </mc-tab>

       <opportunity-evaluations-tab :entity="entity"></opportunity-evaluations-tab>

        <?php $this->part('opportunity-tab-results', ['entity' => $entity]); ?>
        
        <?php $this->part('opportunity-tab-support', ['entity' => $entity]); ?>
        <?php $this->applyTemplateHook("tabs", "end")?>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>
