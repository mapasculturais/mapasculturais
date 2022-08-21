<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 

$this->import('
    mapas-container mc-map mc-map-marker mapas-breadcrumb tabs
    entity-terms share-links entity-files-list entity-links
    entity-location entity-owner entity-related-agents entity-seals 
    entity-header entity-gallery entity-gallery-video entity-social-media
    ');

$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('panel', 'spaces')],
    ['label'=> $entity->name, 'url' => $app->createUrl('space', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>

    <tabs class="tabs">  

        <tab icon="map" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">

                <mapas-container>        
                    <main>
                        <div class="grid-12">
                            <div class="placeholder-hour  col-12 ">
                                <label>Horário de Funcionamento</label>
                                <div class="placeholder-hour--content">
                                    &nbsp;
                                </div>
                            </div>
                            <div class="col-12">
                                <entity-location :entity="entity"></entity-location>
                            </div>
                            <div class="placeholder-resource col-12">
                                <label>Recursos de Acessibilidade</label>
                                <div class="placeholder-resource--content">
                                    <p>CAPACIDADE DO ESPAÇO:</p>
                                    <p>TELEFONE:</p>
                                </div>
                            </div>
                            <div class="divider col-12"></div>
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
                            <div class="property col-12">
                                <button class="button button--primary button--md">Reinvindicar Propriedade</button>
                            </div>
                            
                        </div>
                    </main>
                    
                    <aside>
                        <div class="grid-12">
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
                            
                            <div class="col-12">
                                <entity-owner title="Publicado por" :entity="entity"></entity-links>
                            </div>
                        </div>
                    </aside>
                </mapas-container>
                
            </div>
        </tab>
    
        <tab icon="events" label="<?= i::_e('Agenda') ?>" slug="agenda">
            <div class="tabs__agenda">

            </div>
        </tab>

    </tabs>

    
</div>
