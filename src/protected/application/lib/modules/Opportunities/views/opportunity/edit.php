<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    mapas-breadcrumb
    entity-header
    tabs
    entity-cover
    entity-profile
    entity-field
    entity-terms
    entity-social-media
    entity-seals
    entity-terms
    entity-related-agents
    entity-owner
    entity-files-list
    entity-gallery-video
    entity-gallery
    entity-links
');

$this->breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label'=> $entity->name, 'url' => $app->createUrl('opportunity', 'edit', [$entity->id])],
];
?>

<div class="main-app">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity" editable></entity-header>
    <tabs class="tabs">
        <tab label="<?= i::__('Informações') ?>" slug="info">
            <mapas-container>
                <main>
                    <mapas-card>
                        <template #content>
                            <div class="left">
                                <div class="grid-12 v-bottom">
                                    <entity-cover :entity="entity" classes="col-12"></entity-cover>
                                    <div class="col-3 sm:col-12">
                                        <entity-profile :entity="entity"></entity-profile>
                                    </div>
                                    <div class="col-9 sm:col-12">
                                        <entity-field :entity="entity" prop="name"></entity-field>
                                        <entity-field :entity="entity" editable label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
                                    </div>
                                    <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                                    <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Adicionar regulamento');?>" editable></entity-files-list>
                                </div>
                            </div>
                        </template>
                    </mapas-card>
                    <mapas-card>
                        <div class="grid-12">
                            <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Adicionar arquivos');?>" editable></entity-files-list>
                            <div class="col-12">
                                <entity-links :entity="entity" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
                            </div>
                            <entity-gallery-video :entity="entity" classes="col-12" editable></entity-gallery-video>
                            <entity-gallery :entity="entity" classes="col-12" editable></entity-gallery>
                        </div>
                    </mapas-card>
                </main>
                <aside>
                    <div class="grid-12">
                        <entity-terms :entity="entity" taxonomy="area" classes="col-12" title="<?php i::esc_attr_e('Áreas de interesse'); ?>" editable></entity-terms>
<!--                        <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>-->
<!--                        <entity-seals :entity="entity" classes="col-12" title="--><?php //i::esc_attr_e('Verificações');?><!--" editable></entity-seals>-->
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags')?>" editable></entity-terms>
                        <entity-related-agents editable :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>" editable></entity-related-agents>
                        <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                    </div>
                </aside>
            </mapas-container>
        </tab>
        <tab label="<?= i::__('Configuração de fases') ?>" slug="config">
        </tab>
        <tab label="<?= i::__('Inscrições e Resultados') ?>" slug="subs_result">
        </tab>
        <tab label="<?= i::__('Relatórios') ?>" slug="report">
        </tab>
    </tabs>
</div>
