<?php
use MapasCulturais\i;
$this->layout = 'entity';
$this->import('
    mapas-container mc-map mc-map-marker space-info mapas-breadcrumb tabs
    entity-terms share-links entity-files-list entity-links
    entity-location entity-owner entity-admins entity-related-agents entity-seals 
    entity-header entity-list entity-gallery entity-gallery-video entity-social-media
    ');

$this->breadcramb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Espaços'), 'url' => $app->createUrl('panel', 'spaces')],
    ['label' => $entity->name, 'url' => $app->createUrl('space', 'single', [$entity->id])],
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
                            <div class="col-12">
                                <space-info :entity="entity"></space-info>
                            </div>

                            <div class="divider col-12"></div>
                            <div class="col-12">
                                <h2><?= i::_e("Descrição Detalhada"); ?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>

                            <div class="col-12">
                                <entity-files-list :entity="entity" group="downloads" title="<?= i::_e('Arquivos para download'); ?>"></entity-files-list>
                            </div>

                            <div v-if="entity" class="col-12">
                                <entity-gallery-video :entity="entity"></entity-gallery-video>
                            </div>

                            <div class="col-12">
                                <entity-gallery :entity="entity"></entity-gallery>
                            </div>
                            <div class="property col-12">
                                <button class="button button--primary button--md">"<?= i::_e('Reinvindicar Propriedade'); ?>"</button>
                            </div>

                        </div>
                    </main>

                    <aside>
                        <div class="grid-12">
                            <div class="col-12">
                                <entity-social-media :entity="entity"></entity-social-media>
                            </div>

                            <div class="col-12">
                                <entity-seals :entity="entity" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            </div>

                            <div class="col-12">
                                <entity-related-agents :entity="entity" title="<?= i::_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            </div>

                            <div class="col-12">
                                <entity-terms :entity="entity" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            </div>

                            <div class="col-12">
                                <share-links title="<?php i::esc_attr_e('Compartilhar');?>" text="<?= i::_e('Veja este link:'); ?>"></share-links>
                            </div>

                            <div class="col-12">
                                <entity-owner title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity">
                                    </entity-links>
                            </div>

                            <div class="col-12">
                                <entity-admins :entity="entity"></entity-admins>
                            </div>

                            <div v-if="entity.children.length >0 || entity.relatedOpportunities >0" class="col-12">
                                <h4><?php i::_e('Propriedades do Espaço');?></h4>

                                <entity-list  title="<?php i::esc_attr_e('Subespaços');?>" type="space" :ids="entity.children"></entity-list>
    
                                <entity-list title="<?php i::esc_attr_e('Oportunidades');?>" type="opportunity" :ids="entity.relatedOpportunities"></entity-list>
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