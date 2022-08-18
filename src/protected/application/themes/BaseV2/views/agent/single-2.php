<?php 
$this->layout = 'entity'; 
use MapasCulturais\i;
$this->import('
    mapas-container mc-map mc-map-marker  mapas-breadcrumb
    entity-terms share-links entity-files-list entity-links entity-location entity-owner entity-related-agents entity-seals entity-header entity-gallery entity-gallery-video entity-social-media');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Agentes'), 'url' => $app->createUrl('panel', 'agents')],
    ['label'=> $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app single-1">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>

    <mapas-container class="single-1__content">
        <div class="divider"></div>
        
        <main>
            <div class="grid-12">
                <div class="col-12">
                    <entity-location :entity="entity"></entity-location>
                </div>

                <div class="col-12">
                        <h2>Descrição Detalhada</h2>
                        <p>{{entity.longDescription}}</p>
                </div>
                    
                <div class="col-12">
                    <entity-files-list :entity="entity" group="downloads" title="Arquivos para download"></entity-files-list>
                </div>

                <div v-if="entity" class="col-12">
                    <entity-gallery-video :entity="entity"></entity-gallery-video>
                </div>

                <div class="col-12">
                    <entity-gallery :entity="entity"></entity-gallery>
                </div>
                <div class="property col-6">
                    <button class="button button--primary button--md button-large">Reinvindicar Propriedade</button>
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
                    <entity-seals :entity="entity" title="Verificações"></entity-seals>
                </div>
                
                <div class="col-12">
                    <entity-related-agents :entity="entity"  title="Agentes Relacionados"></entity-related-agents>  
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