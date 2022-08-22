<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('
        entity-header entity-cover entity-profile mapas-breadcrumb
        entity-field entity-terms entity-social-media entity-actions
        entity-links entity-gallery entity-gallery-video
        entity-admins entity-related-agents entity-owner entity-files-list
        mapas-container mapas-card');
        
$this->breadcramb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Projetos'), 'url' => $app->createUrl('panel', 'projects')],
    ['label'=> $entity->name, 'url' => $app->createUrl('project', 'edit', [$entity->id])],
];
?>

<div class="main-app">

    <mapas-breadcrumb></mapas-breadcrumb>
    
    <entity-header :entity="entity" :editable="true"></entity-header>

    <mapas-container>

        <mapas-card class="feature">
            <template #title>
                <label><?php i::_e("Informações de Apresentação")?></label>
                <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>

                <div class="left">
                    <div class="grid-12 v-center">
                        <div class="col-12">
                            <entity-cover :entity="entity"></entity-cover>
                        </div>

                        <div class="col-3 sm:col-12">
                            <entity-profile :entity="entity"></entity-profile>
                        </div>

                        <div class="col-9 sm:col-12">
                            <div class="grid-12">
                                <div class="col-12">
                                    <entity-field :entity="entity" label="<?php i::_e("Nome do projeto") ?>" prop="name"></entity-field>
                                </div>
                                
                                <div class="col-12">
                                    <entity-field :entity="entity" label="<?php i::_e("Tipo do projeto") ?>" prop="type"></entity-field>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <entity-field :entity="entity" prop="shortDescription"></entity-field>
                        </div>

                        <div class="col-12">
                            <entity-field :entity="entity" label="<?php i::_e("Link para página ou site do projeto") ?>" prop="site"></entity-field>
                        </div>
                    </div>                       
                </div>

                <div class="divider"></div>

                <div class="right">
                    <div class="grid-12">
                        <div class="col-12">
                            <entity-social-media :entity="entity" :editable="true"></entity-social-media>
                        </div>
                    </div>
                </div>  

            </template>
        </mapas-card>

        <main>         

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Período de execução do projeto"); ?></label>
                </template>
                <template #content>   
                    <div class="grid-12">
                        <div class="col-6">
                            <entity-field :entity="entity" label="<?php i::_e('Data inicial')?>" prop="registrationFrom"></entity-field>
                        </div>

                        <div class="col-6">
                            <entity-field :entity="entity" label="<?php i::_e('Data Final')?>" prop="registrationTo"></entity-field>
                        </div>
                    </div>
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Adicione atividades para o seu projeto"); ?></label>
                    <p><?php i::_e("Crie um projeto com informações básicas e de forma rápida."); ?></p>
                </template>
                <template #content>   
                
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Adicione um projeto que integrará este**"); ?></label>
                    <p><?php i::_e("Selecione um projeto para que o seu projeto atual seja vinculado como integrante"); ?></p>
                </template>
                <template #content>   
                
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Adicione um subprojeto"); ?></label>
                    <p><?php i::_e("Adicione um projeto que será vinculado a este"); ?></p>
                </template>
                <template #content>   
                
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Vincule um evento ao seu projeto"); ?></label>
                </template>
                <template #content>   
                
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Contatos do projeto"); ?></label>
                    
                </template>
                <template #content>   
                
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <label><?php i::_e("Mais informações públicas"); ?></label>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <div class="col-12">
                            <entity-field :entity="entity" label="<?php i::_e('Descrição')?>" prop="longDescription"></entity-field>
                        </div>

                        <div class="col-12">
                            <entity-files-list :entity="entity" group="downloads" title="<?= i::_e('Adicionar arquivos para download')?>" :editable="true"></entity-files-list>
                        </div>
                        
                        <div class="col-12">
                            <entity-links title="<?php i::_e('Adicionar links')?>" :entity="entity" :editable="true"></entity-links>
                        </div>
                        
                        <div class="col-12">
                            <entity-gallery-video title="<?php i::_e('Adicionar vídeos') ?>" :entity="entity" :editable="true"></entity-gallery-video>
                        </div>
                        
                        <div class="col-12">
                            <entity-gallery title="<?php i::_e('Adicionar fotos na galeria') ?>" :entity="entity" :editable="true"></entity-gallery>
                        </div>
                    </div>
                </template>
            </mapas-card>
            
        </main>

        <aside>            
            <mapas-card>
                <template #content>   
                    <div class="grid-12">
                        <div class="col-12">
                            <entity-admins :entity="entity" :editable="true"></entity-admins>
                        </div>
                        
                        <div class="col-12">
                            <entity-terms :entity="entity" taxonomy="tag" title="<?php i::_e('Tags')?>" :editable="true"></entity-terms>
                        </div>
                        
                        <div class="col-12">
                            <entity-related-agents :entity="entity" :editable="true"></entity-related-agents>
                        </div>
                        
                        <div class="col-12">
                            <entity-owner :entity="entity" title="<?php i::_e('Publicado por')?>" :editable="true"></entity-owner>
                        </div>
                    </div>
                </template>   
            </mapas-card>            
        </aside>

    </mapas-container>

    <entity-actions :entity="entity"></entity-actions>

</div>