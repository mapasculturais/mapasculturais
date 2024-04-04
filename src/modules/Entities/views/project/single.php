<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    complaint-suggestion
    entity-actions
    entity-admins
    entity-card 
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-list
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    opportunity-list
    mc-breadcrumb
    mc-link
    mc-container
    mc-share-links
    mc-tab
    mc-tabs
');

$label = $this->isRequestedEntityMine() ? i::__('Meus projetos') : i::__('Projetos');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'single', [$entity->id])],
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
                <dt><?= i::__('Projeto integrante de') ?></dt>
                <dd><mc-link :entity="entity.parent"></mc-link></dd>
            </dl>
        </template>
    </entity-header>
    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <div class="tabs__info">
                <mc-container>
                    <main>
                        <opportunity-list></opportunity-list>
                        <div class="grid-12">
                            <div v-if="entity.emailPublico || entity.telefonePublico" class="col-12 additional-info">
                                <h4 class="additional-info__title"><?php i::_e("Informações adicionais"); ?></h4>

                                <div v-if="entity.telefonePublico" class="additional-info__item">
                                    <p class="additional-info__item__title"><?php i::_e("telefone:"); ?></p>
                                    <p class="additional-info__item__content">{{entity.telefonePublico}}</p>
                                </div>  

                                <div v-if="entity.emailPublico" class="additional-info__item">
                                    <p class="additional-info__item__title"><?php i::_e("email:"); ?></p>
                                    <p class="additional-info__item__content">{{entity.emailPublico}}</p>
                                </div>
                            </div>
                            <div v-if="entity.longDescription!=null" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada'); ?></h2>
                                <p v-html="entity.longDescription" class="single-project__longdescription"></p>

                            </div>
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download'); ?>"></entity-files-list>
                            <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <mc-share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
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

        <mc-tab label="<?= i::_e('Subprojetos') ?>" slug="subprojects">
            <div class="single-project__subproject">
                <mc-container>
                    <main class="grid-12">
                        <mc-entities v-if="entity.children" type="project" select="name,type,shortDescription,files.avatar,seals,terms" :query="{id: `IN(${entity.children})`}" :limit="20" watch-query>
                            <template #default="{entities}">
                                <entity-card :entity="entity" v-for="entity in entities" :key="entity.__objectId" class="col-12">
                                    <template #avatar>
                                        <mc-avatar :entity="entity" size="medium"></mc-avatar>
                                    </template>
                                    <template #type> 
                                        <span> 
                                            <?= i::__('TIPO: ') ?> 
                                            <span :class="['upper', entity.__objectType+'__color']">{{entity.type.name}}</span>
                                        </span>
                                    </template>
                                </entity-card>
                            </template>                                
                        </mc-entities>

                        <div v-if="!entity.children" class="single-project__not-found">
                            <p class="semibold"><?= i::__('Nenhum subprojeto vinculado.') ?></p>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <mc-share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                        </div>
                    </aside>
                </mc-container>
            </div>
        </mc-tab>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>