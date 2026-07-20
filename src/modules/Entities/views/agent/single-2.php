<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    agent-data-2
    country-address-view
    complaint-suggestion
    entity-actions
    entity-admins
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-list
    entity-location
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-share-links
    mc-tab
    mc-tabs
    opportunity-list
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mc-container>
                    <main>
                        <opportunity-list></opportunity-list>
                        <div class="grid-12">
                            <agent-data-2 :entity="entity"></agent-data-2>
                            <?php $this->applyTemplateHook('single2-agent-documents','before') ?>
                            <template v-if="entity.currentUserPermissions.viewPrivateData">
                                <div class="col-12 agent-data">
                                    <div class="agent-data__secondTitle">
                                        <h4 class="title bold"><?php i::_e("Documentos e Certidões") ?></h4>
                                    </div>
                                    <entity-files-list v-if="entity.files?.['docs-certidao-fiscal']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-fiscal" seal-prop="certidaoFiscalAnexo" title="<?php i::_e('Certidão de Regularidade Fiscal'); ?>"></entity-files-list>
                                    <entity-files-list v-if="entity.files?.['docs-certidao-trabalhista']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-trabalhista" seal-prop="certidaoTrabalhistaAnexo" title="<?php i::_e('Certidão de Regularidade Trabalhista'); ?>"></entity-files-list>
                                    <entity-files-list v-if="entity.files?.['docs-certidao-contas']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-contas" seal-prop="certidaoPrestacaoContasAnexo" title="<?php i::_e('Certidão de Prestação de Contas'); ?>"></entity-files-list>
                                </div>
                            </template>
                            <?php $this->applyTemplateHook('single2-agent-documents','after') ?>
                            <country-address-view v-if="entity.publicLocation" :entity="entity" class="col-12"></country-address-view>
                            <div v-if="entity.longDescription" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada'); ?></h2>
                                <p class="description"  v-html="entity.longDescription"></p>
                            </div>
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download'); ?>"></entity-files-list>
                            <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                            <div v-if="entity.spaces?.length > 0 || entity.children?.length > 0 || entity.events?.length > 0 || entity.projects?.length > 0" class="col-12">
                                <h4 class="property-list"> <?php i::_e('Propriedades do Agente:');?> </h4>
                                <entity-list v-if="entity.spaces?.length>0" title="<?php i::esc_attr_e('Espaços');?>" type="space" :ids="entity.spaces"></entity-list>
                                <entity-list v-if="entity.events?.length>0" title="<?php i::esc_attr_e('Eventos');?>" type="event" :ids="entity.events"></entity-list>
                                <entity-list v-if="entity.children?.length>0" title="<?php i::esc_attr_e('Agentes');?>" type="agent" :ids="entity.children"></entity-list>
                                <entity-list v-if="entity.projects?.length>0" title="<?php i::esc_attr_e('Projetos');?>" type="project" :ids="entity.projects"></entity-list>                                
                            </div>
                            <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area','before') ?>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação'); ?>"></entity-terms>
                            <?php $this->applyTemplateHook('single2-entity-info-taxonomie-area','after') ?>
                            
                            <?php $this->applyTemplateHook('single2-entity-info-social-media','before') ?>
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <?php $this->applyTemplateHook('single2-entity-info-social-media','after') ?>
                            
                            <?php $this->applyTemplateHook('single2-entity-info-entity-seals','before') ?>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-seals','before') ?>

                            <?php $this->applyTemplateHook('single2-entity-info-entity-related-agents','before') ?>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-related-agents','before') ?>

                            <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag','before') ?>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-terms-tag','after') ?>
                            
                            <?php $this->applyTemplateHook('single2-entity-info-mc-share-links','before') ?>
                            <mc-share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                            <?php $this->applyTemplateHook('single2-entity-info-mc-share-links','before') ?>

                            <?php $this->applyTemplateHook('single2-entity-info-entity-admins','before') ?>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-admins','after') ?>
                            
                            <?php $this->applyTemplateHook('single2-entity-info-entity-owner','before') ?>
                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                            <?php $this->applyTemplateHook('single2-entity-info-entity-owner','before') ?>

                        </div>
                    </aside>
                </mc-container>
            </div>
        </mc-tab>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>