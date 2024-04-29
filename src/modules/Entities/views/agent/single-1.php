<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    agent-data-1
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

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <main>
                    <opportunity-list></opportunity-list>
                    <div class="grid-12 col-12">
                        <agent-data-1 :entity="entity"></agent-data-1>
                        <entity-location :entity="entity" classes="col-12"></entity-location>
                        <div v-if="entity.longDescription" class="col-12">
                            <span>   
                                <h3 class="single-1__description bold"><?php i::_e('Descrição Detalhada');?></h3>
                            </span>
                            <p class="single-1__longdescription" v-html="entity.longDescription"></p>
                        </div>
                        <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                        <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                        <div v-if="entity.spaces?.length > 0 || entity.children?.length > 0 || entity.events?.length > 0" class="col-12">
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
                        <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação');?>"></entity-terms>
                        <entity-terms :entity="entity" hide-required taxonomy="funcao" classes="col-12" title="<?php i::_e('Funções'); ?>"></entity-terms>
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>                            
                        <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                        <mc-share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></mc-share-links>
                        <entity-admins :entity="entity" classes="col-12"></entity-admins>
                        <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>                        
                    </div>
                </aside>
            </mc-container>
        </mc-tab>    
    </mc-tabs>   
    <entity-actions :entity="entity"></entity-actions>         
</div>