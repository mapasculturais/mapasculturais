<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    complaint-suggestion
    entity-actions
    entity-admins
    entity-card
    entity-data 
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

$children_id = [];
foreach($entity->children as $children) {
    $children_id[] = $children->id;
}

$children_id  = implode(",", $children_id );

?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl v-if="entity.id && global.showIds[entity.__objectType]" class="metadata__id">
                <entity-data class="metadata__id" :entity="entity" prop="id" label="<?php i::_e("ID:")?>"></entity-data>
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
                                <entity-data v-if="entity.telefonePublico" class="additional-info__item" :entity="entity" prop="telefonePublico" label="<?php i::_e("telefone:")?>"></entity-data>
                                <entity-data v-if="entity.emailPublico" class="additional-info__item" :entity="entity" prop="emailPublico" label="<?php i::_e("email:")?>"></entity-data>
                            </div>
                            <div v-if="entity.longDescription!=null" class="col-12">
                                <entity-data v-if="entity.longDescription!=null" class="additional-info__item col-12" :entity="entity" prop="longDescription" label="<?php i::_e("Descrição Detalhada")?>"></entity-data>
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

        <mc-tab icon="list" label="<?= i::_e('Subprojetos') ?>" slug="subprojects">
            <div class="single-project__subproject">
                <mc-container>
                    <main class="grid-12">
                        <mc-entities v-if="entity.children" type="project" select="name,type,shortDescription,files.avatar,seals,terms" :query="{id: `IN(<?=$children_id?>)`}" :limit="20" watch-query>
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

        <mc-tab icon="event" label="<?= i::_e('Eventos') ?>" slug="events">
            <div class="single-project__events">
                <mc-container>
                    <main class="grid-12">
                        <mc-entities type="event" select="name,shortDescription,files.avatar,seals,terms,occurrences,project" :query="{project: `EQ(${entity.id})`, status: 'EQ(1)'}" :limit="20" watch-query>
                            <template #default="{entities}">
                                <div v-if="entities.length > 0" class="col-12">
                                    <entity-card :entity="event" v-for="event in entities" :key="event.__objectId" class="col-12">
                                        <template #avatar>
                                            <mc-avatar :entity="event" size="medium"></mc-avatar>
                                        </template>
                                        <template #type> 
                                            <span> 
                                                <?= i::__('EVENTO') ?> 
                                                <span class="event__status">{{event.status == 1 ? '<?= i::__('Ativo') ?>' : '<?= i::__('Inativo') ?>'}}</span>
                                            </span>
                                        </template>
                                        <template #extra-content>
                                            <div v-if="event.occurrences && event.occurrences.length > 0" class="event__occurrences">
                                                <h5><?= i::__('Ocorrências:') ?></h5>
                                                <div v-for="occurrence in event.occurrences" :key="occurrence.id" class="occurrence">
                                                    <strong>{{occurrence.space?.name || '<?= i::__('Local não informado') ?>'}}</strong>
                                                    <div class="occurrence__datetime">
                                                        <span v-if="occurrence.startsAt">{{occurrence.startsAt | formatDate}}</span>
                                                        <span v-if="occurrence.startsAt && occurrence.endsAt"> - </span>
                                                        <span v-if="occurrence.endsAt">{{occurrence.endsAt | formatDate}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </entity-card>
                                </div>
                                <div v-else class="single-project__not-found col-12">
                                    <p class="semibold"><?= i::__('Nenhum evento vinculado a este projeto.') ?></p>
                                </div>
                            </template>                                
                        </mc-entities>
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