<?php
use MapasCulturais\i;
$this->layout = 'entity';
$this->import('entity-files-list entity-admins entity-gallery entity-list
entity-location entity-owner entity-gallery-video 
entity-header mapas-breadcrumb mapas-container
share-links entity-terms entity-related-agents
entity-seals entity-social-media');
$this->breadcramb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Eventos'), 'url' => $app->createUrl('panel', 'events')],
    ['label' => $entity->name, 'url' => $app->createUrl('event', 'edit', [$entity->id])],
];
?>
<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <mapas-container>
        <div class="divider"></div>

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
                    <div v-if="entity.descricaoSonora || entity.traducaoLibras" class="acessibility">
                        <label class="acessibility__label"><?php i::_e("Acessibilidade"); ?></label>
                        <div v-if="entity.descricaoSonora" class="acessibility__audio">
                            <span><?php i::_e("Libras:"); ?></span>{{entity.descricaoSonora}}
                        </div>
                        <div v-if="entity.traducaoLibras" class="acessibility__libras">
                            <span><?php i::_e("Áudio de Descrição:"); ?></span> {{entity.traducaoLibras}}
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <entity-location :entity="entity"></entity-location>
                </div>

                <div v-if="entity.longDescription" class="col-12">
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
                    <entity-seals :entity="entity" title="<?php i::esc_attr_e('Verificações');?>" :editable="entity.currentUserPermissions.createSealRelation"></entity-seals>
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

                <div class="col-12">
                    <entity-owner title="<?php i::esc_attr_e('Publicado por'); ?>" :entity="entity">
                        </entity-links>
                </div>
        </aside>
    </mapas-container>
</div>