<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('
    mapas-container mapas-card mapas-breadcrumb
    entity-field entity-profile entity-cover entity-terms 
    entity-admins entity-header entity-actions entity-owner 
    entity-social-media entity-related-agents entity-links
    entity-gallery entity-gallery-video');

$this->breadcramb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Agentes'), 'url' => $app->createUrl('panel', 'agents')],
    ['label'=> $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app edit-1">

    <mapas-breadcrumb></mapas-breadcrumb>
    
    <messages></messages>
    
    <entity-header :entity="entity" :editable="true"></entity-header>

    <mapas-container class="edit-1__content">

        <mapas-card class="card-1">
            <template #title>
                <h3 class="card__title--title"><?php i::_e("Informações de Apresentação")?></h3>
                <p class="card__title--description"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>
                
                <div class="card-1__left">

                    <div class="row">
                        <entity-cover :entity="entity"></entity-cover>
                    </div>    
                    
                    <div class="row">
                        <entity-profile :entity="entity"></entity-profile>
                        <entity-field :entity="entity" prop="name"></entity-field>
                    </div>
                    
                    <div class="row">
                        <entity-field :entity="entity" prop="shortDescription"></entity-field>
                    </div>  
                    <div class="row">
                        <entity-field :entity="entity" prop="site"></entity-field>
                    </div>
                    
                </div>

                <div class="card-1__divider"></div>

                <div class="card-1__right">
                    <entity-terms :entity="entity" taxonomy="area" :editable="true" title="Áreas de interesse"></entity-terms>
                    <entity-social-media :entity="entity" :editable="true"></entity-social-media>
                </div>
                 

            </template>
        </mapas-card>

        <main>
            <mapas-card>
                <template #title>
                    <h2><?php i::_e("Dados Pessoais"); ?></h2>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistema e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>                
                    <div class="row">
                        <entity-field :entity="entity" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="documento"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="emailPrivado"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="telefonePublico"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="emailPublico"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="telefone1"></entity-field>
                        <entity-field :entity="entity" prop="telefone2"></entity-field>
                    </div>
                    divider
                    <div class="row">
                        <entity-field :entity="entity" prop="En_CEP"></entity-field>
                        <entity-field :entity="entity" prop="En_Nome_Logradouro"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="En_Num"></entity-field>
                        <entity-field :entity="entity" prop="En_Bairro"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="En_Complemento"></entity-field>
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="En_Municipio"></entity-field>
                        <entity-field :entity="entity" prop="En_Estado"></entity-field>
                    </div>
                    <div class="row">
                        <!-- <entity-field :entity="entity" prop="publicLocation"></entity-field> -->
                    </div>
                    <div class="row">
                        <entity-field :entity="entity" prop="longDescription"></entity-field>
                    </div>
                </template>
            </mapas-card>

            <mapas-card>
                <template #title>
                    <h3><?php i::_e("Dados pessoais sensíveis"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="row no-breakline">
                        <entity-field :entity="entity" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-field>
                        <entity-field :entity="entity" prop="genero" label="<?= i::__('Selecione o Gênero')?>" ></entity-field>
                    </div>
                    <div class="row no-breakline">
                        <entity-field :entity="entity" prop="orientacaoSexual" label="<?= i::__('Selecione a Orientação Sexual') ?>"></entity-field>
                        <entity-field :entity="entity" prop="raca" label="<?= i::__('Selecione a Raça/Cor') ?>"></entity-field>
                    </div>
                </template>
            </mapas-card>

            <mapas-card>
                <template #title>
                    <h3><?php i::_e("Mais informações públicas"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="row">
                        <entity-links title="Adicionar links" :entity="entity" :editable="true"></entity-links>
                    </div>
                    <div class="row">
                        <entity-gallery-video title="<?php i::_e('Adicionar vídeos') ?>" :entity="entity" :editable="true"></entity-gallery-video>
                    </div>
                    <div class="row">
                        <entity-gallery title="<?php i::_e('Adicionar fotos na galeria') ?>" :entity="entity" :editable="true"></entity-gallery>
                    </div>
                </template>
            </mapas-card>
        </main>

        <aside>
            <mapas-card>
                <template #content>

                    <div class="row">
                        <entity-admins :entity="entity" :editable="true"></entity-admins>
                    </div>
                    
                    <div class="row">
                        <entity-terms :entity="entity" taxonomy="tag" title="Tags" :editable="true"></entity-terms>
                    </div>

                    <div class="row">
                        <entity-related-agents :entity="entity" :editable="true"></entity-related-agents>
                    </div>

                    <div class="row">
                        <entity-owner :entity="entity" title="Publicado por" :editable="true"></entity-owner>
                    </div>

                </template>
            </mapas-card>
        </aside>
        
    </mapas-container>    

    <entity-actions :entity="entity" />

</div>
