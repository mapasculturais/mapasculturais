<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    compliant-suggestion
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
    space-info
    tabs
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Espaços'), 'url' => $app->createUrl('search', 'spaces')],
    ['label' => $entity->name, 'url' => $app->createUrl('space', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl>
                <dt><?= i::__('Tipo') ?></dt>
                <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.type.name}} </dd>
            </dl>
            <dl v-if="entity.parent">
                <dt><?= i::__('Espaço integrante de') ?></dt>
                <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.parent.name}} </dd>
            </dl>
        </template>
    </entity-header>
    <tabs class="tabs">
        <tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mapas-container>
                    <main>
                        <div class="grid-12">
                            <div class="col-12">
                                <space-info :entity="entity"></space-info>
                            </div>
                            <div v-if="entity.longDescription" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada');?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?= i::_e('Arquivos para download'); ?>"></entity-files-list>                            
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>                            
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>                            
                            <div v-if="(entity.children && entity.children.length >0) || (entity.relatedOpportunities && entity.relatedOpportunities.length>0)" class="col-12">
                                <h4><?php i::_e('Propriedades do Espaço');?></h4>
                                <entity-list v-if="entity.children?.length>0" title="<?php i::esc_attr_e('Subespaços');?>" type="space" :ids="entity.children"></entity-list>
                                <entity-list title="<php i::esc_attr_e('Oportunidades');?>"  type="opportunity" :ids="[...(entity.ownedOpportunities ? entity.ownedOpportunities : []), ...(entity.relatedOpportunities ? entity.relatedOpportunities : [])]"></entity-list>
                                
                            </div>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?= i::_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?= i::_e('Veja este link:'); ?>"></share-links>
                            <entity-owner :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>"></entity-owner>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                            
                        </div>
                    </aside>
                    <aside>
                        <div class="grid-12">
                            <compliant-suggestion :entity="entity"></compliant-suggestion>
                        </div>
                    </aside>
                </mapas-container>
                <entity-actions :entity="entity"></entity-actions>
            </div>
        </tab>
    </tabs>
</div>