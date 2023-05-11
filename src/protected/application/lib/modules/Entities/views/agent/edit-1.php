<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 

$this->import('
    confirm-before-exit
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
    entity-social-media
    entity-terms
    mapas-breadcrumb
    mapas-card
    mapas-container
');

$this->breadcrumb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Agentes'), 'url' => $app->createUrl('panel', 'agents')],
    ['label'=> $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mapas-container>
        <mapas-card class="feature">
            <template #title>
                <label><?php i::_e("Informações de Apresentação")?></label>
                <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>                
                <div class="left">
                    <div class="grid-12 v-bottom">
                        <entity-cover :entity="entity" classes="col-12"></entity-cover>
                        <div class="col-3 sm:col-12">
                            <entity-profile :entity="entity"></entity-profile>
                        </div>
                        <entity-field :entity="entity" classes="col-9 sm:col-12" prop="name"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="site"></entity-field>
                    </div>                      
                </div>
                <div class="divider"></div>
                <div class="right">
                    <div class="grid-12">
                        <entity-terms :entity="entity" taxonomy="area" editable classes="col-12" title="<?php  i::_e('Áreas de atuação'); ?>"></entity-terms>
                        <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                    </div>
                </div>
            </template>
        </mapas-card>
        <main>
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Dados Pessoais"); ?></label>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistema e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>                
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="cpf"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="cnpj" label="CNPJ (MEI)"></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="emailPrivado"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefonePublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="emailPublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefone1"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefone2"></entity-field>
                        <div class="col-12 divider"></div>
                        <entity-location :entity="entity" classes="col-12" editable></entity-location>
                    </div>
                </template>
            </mapas-card>
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Dados pessoais sensíveis"); ?></label>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicional" label="<?= i::__('Comunidades tradicionais')?>" ></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicionalOutros" label="<?= i::__('Não encontrou sua comunidade Tradicional')?>" ></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="pessoaDeficiente" label="<?= i::__('Pessoa com Deficiência')?>" ></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="escolaridade" label="<?= i::__('Escolaridade')?>" ></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="agenteItinerante" label="<?= i::__('Agente Itinerante')?>" ></entity-field>                        
                        <div class="disabled field col-12">
                            <label><?= i::__("Pessoa idosa")?></label>
                            <input v-if="entity.idoso==1" value="<?= i::__('Sim')?>">
                            <input v-if="entity.idoso==0" value="<?= i::__('Não')?>">
                        </div>
                        <entity-field :entity="entity" classes="col-12" prop="genero" label="<?= i::__('Selecione o Gênero')?>" ></entity-field>                        
                        <entity-field :entity="entity" classes="col-12" prop="orientacaoSexual" label="<?= i::__('Selecione a Orientação Sexual') ?>"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="raca" label="<?= i::__('Selecione a Raça/Cor') ?>"></entity-field>
                    </div>
                </template>
            </mapas-card>
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Mais informações públicas"); ?></label>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" prop="longDescription"></entity-field>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?php i::_e('Adicionar arquivos para download'); ?>" editable></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>   
                        <entity-gallery-video :entity="entity" classes="col-12" title="<?php i::_e('Adicionar vídeos') ?>" editable></entity-gallery-video>                    
                        <entity-gallery :entity="entity" classes="col-12" title="<?php i::_e('Adicionar fotos na galeria') ?>" editable></entity-gallery>
                    </div>
                </template>
            </mapas-card>
        </main>
        <aside>
            <mapas-card>
                <template #content>
                    <div class="grid-12">
                        <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                        <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="Tags" popover-title="<?php i::esc_attr_e('Tags')?>" editable></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                        <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                    </div>
                </template>
            </mapas-card>
        </aside>
    </mapas-container>
    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit unsaved></confirm-before-exit>
