<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    entity-admins
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-list
    entity-occurrence-list
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mapas-breadcrumb
    mapas-container
    share-links
    tabs
');

$this->breadcramb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Eventos'), 'url' => $app->createUrl('search', 'events')],
    ['label' => $entity->name, 'url' => $app->createUrl('event', 'edit', [$entity->id])],
];
?>
<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity">
        <template #metadata>
            <dl>
                <dd>{{entity.subTitle}}</dd>
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
                                <div class="age-rating">
                                    <label class="age-rating__label">
                                        <?= i::_e("Classificação Etária"); ?>
                                    </label>
                                    <div class="age-rating__content">
                                        {{entity.classificacaoEtaria}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <entity-occurrence-list :entity="entity"></entity-occurrence-list>
                            </div>
                            
                            <div class="col-12">
                                <div class="acessibility">
                                    <span class="acessibility__label"><?php i::_e("Acessibilidade"); ?></label>
                                    <div v-if="entity.descricaoSonora" class="acessibility__audio">
                                        <span><?php i::_e("Libras:"); ?></span>{{entity.descricaoSonora}}
                                    </div>
                                    <div v-if="entity.traducaoLibras" class="acessibility__libras">
                                        <span><?php i::_e("Áudio de Descrição:"); ?></span> {{entity.traducaoLibras}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 acessibility ">
                                <div v-if="entity.event_attendance || entity.telefonePublico || entity.registrationInfo" class="event_info__infos">
                                    <span class="acessibility__label"><?php i::_e("Informações adicionais"); ?></span>
                                    <div v-if="entity.event_attendance" class="acessibility__attendance">
                                        <span><?php i::_e("Total de público:"); ?></span> {{entity.event_attendance}}
                                    </div>
                                    <div v-if="entity.telefonePublico" class="acessibility__phone">
                                        <span><?php i::_e("telefone:"); ?></span> {{entity.telefonePublico}}
                                    </div>
                                    <div v-if="entity.registrationInfo" class="acessibility__infos">
                                        <span><?php i::_e("Informações sobre a inscrição:"); ?></span> {{entity.registrationInfo}}
                                    </div>
                                </div>
                            </div>

                            <div v-if="entity.longDescription" class="col-12 longDescription">
                                <h2><?php i::_e('Descrição Detalhada');?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>
                          
                            <div class="col-12">
                                <entity-files-list :entity="entity" group="downloads" title="<?php i::esc_attr_e('Arquivos para download') ?>"></entity-files-list>
                            </div>

                            

                            <div class="col-12">
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
                                <entity-terms :entity="entity" taxonomy="area" title="<?php i::esc_attr_e('Areas de atuação');?>"></entity-terms>
                            </div>

                            <div class="col-12">
                                <entity-social-media :entity="entity"></entity-social-media>
                            </div>

                            <div class="col-12">
                                <entity-seals :entity="entity" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            </div>

                            <div class=col-12>
                                <entity-related-agents :entity="entity" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            </div>

                            <div class="col-12">
                                <entity-terms :entity="entity" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            </div>

                            <div class="col-12">
                                <share-links title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                            </div>

                            <div class="col-12">
                                <entity-admins :entity="entity"></entity-admins>
                            </div>

                            <div v-if="entity.relatedOpportunities.length > 0" class="col-12">
                                <h4><?php i::_e('Propriedades do Evento');?></h4>

                                <entity-list title="<?php i::esc_attr_e('Oportunidades'); ?>" type="opportunity" :ids="entity.relatedOpportunities"></entity-list>
                            </div>

                            <entity-owner classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity"></entity-owner>
                    </aside>
                </mapas-container>

                <entity-actions :entity="entity"></entity-actions>

            </div>  
        </tab>
    </tabs>        
</div>