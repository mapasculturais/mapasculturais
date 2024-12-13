<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit
    elderly-person
    entity-actions
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-location
    entity-owner
    entity-profile
    entity-related-agents
    entity-renew-lock
    entity-social-media
    entity-terms
    entity-status
    mc-breadcrumb
    mc-card
    mc-container
    mc-tabs
    mc-tab

');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <mc-card class="feature">
                    <template #title>
                        <label><?php i::_e("Informações de Apresentação") ?></label>
                        <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
                    </template>
                    <template #content>
                        <div class="left">
                            <div class="grid-12 v-bottom">
                                <entity-cover :entity="entity" classes="col-12"></entity-cover>

                                <div class="col-12 grid-12">
                                    <?php $this->applyTemplateHook('entity-info','begin') ?>
                                    <div class="col-3 sm:col-12">
                                        <entity-profile :entity="entity"></entity-profile>
                                    </div>
                                    <div class="col-9 sm:col-12 grid-12 v-bottom">
                                        <entity-field :entity="entity" classes="col-12" prop="name" label="<?php i::_e('Nome do Agente') ?>"></entity-field>
                                    </div>
                                    <?php $this->applyTemplateHook('entity-info','end') ?>
                                </div>
                                
                                <?php $this->applyTemplateHook('edit1-entity-info-taxonomie-area','before') ?>
                                <entity-terms :entity="entity" taxonomy="area" editable classes="col-12" title="<?php i::_e('Áreas de atuação'); ?>"></entity-terms>
                                <?php $this->applyTemplateHook('edit1-entity-info-taxonomie-area','after') ?>

                                <?php $this->applyTemplateHook('edit1-entity-info-shortDescription','before') ?>
                                <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400" label="<?php i::_e('Mini bio') ?>">
                                    <template #info> 
                                        <?php $this->info('cadastro -> cadastrando-usuario -> mini-bio') ?>
                                    </template>
                                </entity-field>
                                <?php $this->applyTemplateHook('edit1-entity-info-shortDescription','after') ?>

                                <?php $this->applyTemplateHook('edit1-entity-info-site','before') ?>
                                <entity-field :entity="entity" classes="col-12" prop="site"></entity-field>
                                <?php $this->applyTemplateHook('edit1-entity-info-site','after') ?>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="right">
                            <div class="grid-12">
                                <entity-terms :entity="entity" taxonomy="funcao" editable classes="col-12" title="<?php i::_e('Informe sua função na cultura'); ?>"></entity-terms>
                                <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                            </div>
                        </div>
                    </template>
                </mc-card>
                <main>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Dados Pessoais"); ?> <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais') ?></h3>
                            <p><?php i::_e("Não se preocupe, esses dados não serão exibidos publicamente."); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" prop="nomeSocial" label="<?= i::__('Nome Social') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></entity-field>
                                <entity-field v-if="global.auth.is('admin')" :entity="entity" prop="type" @change="entity.save(true).then(() => global.reload())" classes="col-12"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="cpf"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="cnpj" label="<?= i::__('MEI (CNPJ do MEI)') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="emailPrivado" label="<?= i::__('E-mail pessoal') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="telefonePublico" label="<?= i::__('Telefone público com DDD') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="emailPublico" label="<?= i::__('E-mail público') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone1" label="<?= i::__('Telefone privado 1 com DDD') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone2" label="<?= i::__('Telefone privado 2 com DDD') ?>"></entity-field>
                                <div class="col-12 divider"></div>
                                <entity-location :entity="entity" classes="col-12" editable></entity-location>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Dados pessoais sensíveis"); ?> <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais-sensiveis') ?></h3>
                            <p class="data-subtitle"><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-field>
                                <elderly-person :entity="entity" ></elderly-person>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="genero" label="<?= i::__('Selecione o Gênero') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="orientacaoSexual" label="<?= i::__('Selecione a Orientação Sexual') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="raca" label="<?= i::__('Selecione a Raça/Cor') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="escolaridade" label="<?= i::__('Selecione a sua Escolaridade') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="agenteItinerante" label="<?= i::__('É agente itinerante?') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="pessoaDeficiente" class="pcd col-12" label="<?= i::__('Pessoa com Deficiência') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicional" label="<?= i::__('Comunidades tradicionais') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicionalOutros" label="<?= i::__('Não encontrou sua comunidade Tradicional') ?>"></entity-field>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <label><?php i::_e("Informações públicas"); ?></label>
                            <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" prop="longDescription" editable></entity-field>
                                <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?php i::_e('Adicionar arquivos para download'); ?>" editable></entity-files-list>
                                <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
                                <entity-gallery-video :entity="entity" classes="col-12" title="<?php i::_e('Adicionar vídeos') ?>" editable></entity-gallery-video>
                                <entity-gallery :entity="entity" classes="col-12" title="<?php i::_e('Adicionar fotos na galeria') ?>" editable></entity-gallery>
                            </div>
                        </template>
                    </mc-card>
                </main>
                <aside>
                    <mc-card>
                        <template #content>
                            <div class="grid-12">
                                <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                                <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                                <entity-terms :entity="entity" taxonomy="tag" classes="col-12" title="Tags" editable></entity-terms>
                                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                            </div>
                        </template>
                    </mc-card>
                </aside>
            </mc-container>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>
    
    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>