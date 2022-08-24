<?php 
use MapasCulturais\i;
$this->layout = 'entity';
$this->import('entity-files-list entity-gallery 
entity-location entity-owner entity-gallery-video 
entity-header entity-request-ownership mapas-breadcrumb mapas-container
share-links entity-terms entity-related-agents
entity-seals entity-social-media');
$this->breadcramb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Eventos'), 'url' => $app->createUrl('panel', 'events')],
    ['label'=> $entity->name, 'url' => $app->createUrl('event', 'edit', [$entity->id])],
];
?>
<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <mapas-container>
        <div class="divider"></div>

        <main>
            <div class="grid-12">
                <div class="col-12">
                    <entity-location :entity="entity"></entity-location>
                </div>
                
                <div v-if="entity.longDescription" class="col-12">
                    <h2>Descrição Detalhada</h2>
                    <p>{{entity.longDescription}}</p>
                </div>
                
                <div class="col-12">
                    <entity-files-list :entity="entity" group="downloads" title="Arquivos para download"></entity-files-list>
                </div>

                <div class="col-12">
                    <entity-gallery-video :entity="entity"></entity-gallery-video>
                </div>

                <div class="col-12">
                    <entity-gallery :entity="entity"></entity-gallery>
                </div>
                <div class="property col-12">
                    <entity-request-ownership></entity-request-ownership>
                </div>
            </div>
        </main>

        <aside>         
            <div class="grid-12">
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="area" title="Areas de atuação"></entity-terms>
                </div>
                
                <div class="col-12">
                    <entity-social-media :entity="entity"></entity-social-media>
                </div>
                
                <div class="col-12">
                    <entity-seals :entity="entity" title="Verificações" :editable="entity.currentUserPermissions.createSealRelation"></entity-seals>
                </div>
                
                <div class=col-12>
                    <entity-related-agents :entity="entity" title="Agentes Relacionados"></entity-related-agents>
                </div>
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>  
                </div>
                
                <div class="col-12">
                    <share-links title="Compartilhar" text="Veja este link:"></share-links>
                </div>

                <div  class="col-12">
                    <entity-owner title="Publicado por" :entity="entity"></entity-links>
                </div>
            </div>
        </aside>
    </mapas-container>
</div>
