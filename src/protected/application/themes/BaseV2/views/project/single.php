<?php

use MapasCulturais\i;

$this->layout = 'entity';


$this->import('
    mapas-container  mapas-breadcrumb entity-admins
    entity-terms entity-parent share-links entity-files-list entity-links  entity-list entity-owner entity-related-agents entity-seals entity-header entity-gallery entity-gallery-video entity-social-media link-project tabs');
$this->breadcramb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Projetos'), 'url' => $app->createUrl('panel', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'single', [$entity->id])],
];
?>

<div class="main-app single">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <tabs class="tabs">

        <tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mapas-container class="single-1__content">
                    <main>

                        <div class="grid-12">
                            <div class="col-12">
                                <h2><?php i::_e('Descrição Detalhada');?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>

                            <div class="col-12">
                                <entity-files-list :entity="entity" group="downloads" title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
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
                                <entity-seals :entity="entity" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            </div>

                            <div class="col-12">
                                <entity-related-agents :entity="entity" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                            </div>

                            <div class="col-12">
                                <entity-terms :entity="entity" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            </div>

                            <div class="col-12">
                                <share-links title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                            </div>

                            <div class="col-12">
                                <entity-owner title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity">
                                    </entity-links>
                            </div>

                            <div class="col-12">
                                <entity-admins :editable="false" :entity="entity"></entity-admins>
                            </div>

                            <div v-if="entity.relatedOpportunities.length>0 || entity.children.length>0" class="col-12">
                                    <h4><?php i::_e('Propriedades do Projeto');?></h4>

                                    <entity-list title="<?php i::esc_attr_e('Subprojetos');?>" type="project" :ids="entity.children"></entity-list>

                                    <entity-list title="<?php i::esc_attr_e('Oportunidades');?>" type="opportunity" :ids="entity.relatedOpportunities"></entity-list>
                            </div>
                        </div>
                    </aside>
                </mapas-container>
            </div>
        </tab>
    </tabs>    
</div>