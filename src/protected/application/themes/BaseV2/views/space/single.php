<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
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
    mc-map
    mc-map-marker
    share-links
    space-info
    tabs
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

        <tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">

                <mapas-container>
                    <main>
                        <div class="grid-12">
                            <div class="col-12">
                                <space-info :entity="entity"></space-info>
                            </div>
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
                            
                        </div>
                    </main>

                    <aside>
                        <div class="grid-12">
                            <div class="col-12">
                                <entity-social-media :entity="entity"></entity-social-media>
                            </div>

                            <div class="col-12">
                                <entity-seals :entity="entity" :editable="entity.currentUserPermissions.createSealRelation" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
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

                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>

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
    </tabs>
</div>