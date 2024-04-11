<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
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
    space-info
    mc-tab
    mc-tabs
    opportunity-list
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Espaços'), 'url' => $app->createUrl('search', 'spaces')],
    ['label' => $entity->name, 'url' => $app->createUrl('space', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                <dt class="metadata__id--id"><?= i::__('ID') ?></dt>
                    <dd><strong>{{entity.id}}</strong></dd>
            </dl> 
            <dl v-if="entity.type">
                <dt><?= i::__('Tipo') ?></dt>
                <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.type.name}} </dd>
            </dl>
            <dl v-if="entity.parent">
                <dt><?= i::__('Espaço integrante de') ?></dt>
                <dd :class="[entity.__objectType+'__color', 'type']"> {{entity.parent.name}} </dd>
            </dl>
        </template>
    </entity-header>
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mc-container>
                    <main>
                        <opportunity-list></opportunity-list>
                        <div class="grid-12">
                            <div class="col-12">
                                <space-info :entity="entity"></space-info>
                            </div>
                            <div v-if="entity.longDescription" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada');?></h2>
                                <p v-html="entity.longDescription" class="single-space__longdescription"></p>
                            </div>
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?= i::_e('Arquivos para download'); ?>"></entity-files-list>                            
                            <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>                            
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>                            
                            <div v-if="entity.children && entity.children.length >0" class="col-12">
                                <h4><?php i::_e('Propriedades do Espaço');?></h4>
                                <entity-list v-if="entity.children?.length>0" title="<?php i::esc_attr_e('Subespaços');?>" type="space" :ids="entity.children"></entity-list>
                            </div>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-owner :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Publicado por');?>"></entity-owner>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação') ?>"></entity-terms>
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?= i::_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="Tags"></entity-terms>
                            <mc-share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?= i::_e('Veja este link:'); ?>"></mc-share-links>                            
                        </div>
                    </aside>
                    <aside>
                        <div class="grid-12">
                            <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>
                        </div>
                    </aside>
                </mc-container>
            </div>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>