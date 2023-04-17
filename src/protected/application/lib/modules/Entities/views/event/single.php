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

$this->breadcrumb = [
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
                                <div v-if="entity.descricaoSonora || entity.traducaoLibras" class="acessibility">
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
                            <entity-files-list v-if="entity.files.downloads!= null" :entity="entity"  classes="col-12" group="downloads" title="<?php i::esc_attr_e('Arquivos para download') ?>"></entity-files-list>
                            <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                            <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                            <div v-if="entity.relatedOpportunities && entity.relatedOpportunities.length > 0" class="col-12">
                                <h4><?php i::_e('Propriedades do Evento');?></h4>
                                <entity-list title="<php i::esc_attr_e('Oportunidades');?>"  type="opportunity" :ids="[...(entity.ownedOpportunities ? entity.ownedOpportunities : []), ...(entity.relatedOpportunities ? entity.relatedOpportunities : [])]"></entity-list>
                                
                            </div>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-terms :entity="entity" classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Areas de atuação');?>"></entity-terms>
                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                            <entity-seals :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                            <share-links classes="col-12" title="<?php i::esc_attr_e('Compartilhar'); ?>" text="<?php i::esc_attr_e('Veja este link:');?>"></share-links>
                            <entity-admins :entity="entity" classes="col-12"></entity-admins>
                            <entity-owner :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Publicado por'); ?>"></entity-owner>
                    </aside>
                </mapas-container>
                <entity-actions :entity="entity"></entity-actions>
            </div>  
        </tab>
    </tabs>        
</div>