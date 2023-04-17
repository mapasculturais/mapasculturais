<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
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
    mapas-breadcrumb
    mapas-container
    share-links
    tabs
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Agentes'), 'url' => $app->createUrl('search', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <tabs class="tabs">
        <tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mapas-container>
                    <main>
                        <div class="grid-12">
                            <entity-location :entity="entity" classes="col-12"></entity-location>
                            <div v-if="entity.cnpj" class="col-12 box-cnpj">
                                <label>CNPJ</label>
                                <div class="box-cnpj__box">
                                    {{entity.cnpj}}
                                </div>
                            </div>
                            <div v-if="entity.longDescription" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada'); ?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download'); ?>"></entity-files-list>
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                            <div v-if="entity.spaces?.length > 0 || entity.children?.length > 0 || entity.events?.length > 0 || entity.ownedOpportunities?.length > 0 || entity.relatedOpportunities?.length > 0" class="col-12">
                                <h4 class="property-list"> <?php i::_e('Propriedades do Agente:');?> </h4>
                                <entity-list v-if="entity.spaces?.length>0" title="<?php i::esc_attr_e('Espaços');?>" type="space" :ids="entity.spaces"></entity-list>
                                <entity-list v-if="entity.events?.length>0" title="<?php i::esc_attr_e('Eventos');?>" type="event" :ids="entity.events"></entity-list>
                                <entity-list v-if="entity.children?.length>0" title="<?php i::esc_attr_e('Agentes');?>" type="agent" :ids="entity.children"></entity-list>
                                <entity-list v-if="entity.projects?.length>0" title="<?php i::esc_attr_e('Projetos');?>" type="project" :ids="entity.projects"></entity-list>                                
                                <entity-list title="<php i::esc_attr_e('Oportunidades');?>"  type="opportunity" :ids="[...(entity.ownedOpportunities ? entity.ownedOpportunities : []), ...(entity.relatedOpportunities ? entity.relatedOpportunities : [])]"></entity-list>
                            </div>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-terms :entity="entity" classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Areas de atuação'); ?>"></entity-terms>
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:'); ?>"></share-links>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                            
                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                        </div>
                    </aside>
                </mapas-container>
                <entity-actions :entity="entity"></entity-actions>
            </div>
        </tab>
    </tabs>
</div>