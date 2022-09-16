<?php
$this->layout = 'entity';

use MapasCulturais\i;

$this->import('
    mapas-container mapas-card mc-map mc-map-marker entity-owner mapas-breadcrumb
    entity-terms share-links entity-admins entity-files-list entity-links entity-list entity-location  entity-related-agents entity-owner entity-gallery-video entity-seals entity-header entity-gallery entity-social-media');
$this->breadcramb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Agentes'), 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app single-1">
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
                    <h2><?php i::_e('Descrição Detalhada');?></h2>
                    <p>{{entity.longDescription}}</p>
                </div>

                <div class="col-12">
                    <entity-files-list :entity="entity" group="downloads" title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                </div>

                <div class="col-12">
                    <entity-gallery-video :entity="entity"></entity-gallery-video>
                </div>

                <div class="col-12">
                    <entity-gallery :entity="entity"></entity-gallery>
                </div>
                <div class="property col-12">
                    <button class="button button--primary button--md button-large"><?php i::_e('Reinvindicar Propriedade');?></button>
                </div>
            </div>
        </main>

        <aside>
            <div class="grid-12">
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="area" title="<?php i::esc_attr_e('Areas de atuação');?>"></entity-terms>
                </div>

                <div class="col-12">
                    <entity-social-media :entity="entity"></entity-social-media>
                </div>

                <div class="col-12">
                    <entity-seals :entity="entity" title="<?php i::esc_attr_e('Verificações');?>" :editable="entity.currentUserPermissions.createSealRelation"></entity-seals>
                </div>

                <div class=col-12>
                    <entity-related-agents :entity="entity" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                </div>
                <div class="col-12">
                    <entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>
                </div>

                <div class="col-12">
                    <share-links title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                </div>

                <div class="col-12">
                    <entity-admins :entity="entity"></entity-admins>
                </div>

                <div v-if="entity.spaces.length>0 || entity.children.length>0 || entity.events.length>0 || entity.ownedOpportunities.length > 0 || entity.relatedOpportunities.length >0" class="col-12">
                    <h4><?php i::_e('Propriedades do Agente:');?></h4>
                    <entity-list title="<?php i::esc_attr_e('Espaços');?>" type="space" :ids="entity.spaces"></entity-list>

                    <entity-list title="<?php i::esc_attr_e('Eventos');?>" type="event" :ids="entity.events"></entity-list>

                    <entity-list title="<?php i::esc_attr_e('Agentes');?>" type="agent" :ids="entity.children"></entity-list>

                    <entity-list title="<?php i::esc_attr_e('Projetos');?>" type="project" :ids="entity.projects"></entity-list>
                    
                    <entity-list title="<?php i::esc_attr_e('Oportunidades');?>"  type="opportunity" :ids="entity.ownedOpportunities.concat(entity.relatedOpportunities)"></entity-list>
                </div>

                <div class="col-12">
                    <entity-owner title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                </div>
            </div>
        </aside>

    </mapas-container>
</div>