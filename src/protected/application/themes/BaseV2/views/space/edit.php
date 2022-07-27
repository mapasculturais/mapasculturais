<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('
        entity-header entity-cover entity-profile 
        entity-field entity-terms entity-social-media 
        entity-links entity-gallery entity-gallery-video
        entity-admins entity-related-agents entity-owner
        entity-actions
        mapas-container mapas-card mapas-breadcrumb
        messages');

$this->breadcramb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Espaços'), 'url' => $app->createUrl('panel', 'spaces')],
    ['label'=> $entity->name, 'url' => $app->createUrl('space', 'edit', [$entity->id])],
];
?>

<div class="main-app">

    <mapas-breadcrumb></mapas-breadcrumb>
    
    <messages></messages>

    <entity-header :entity="entity" :editable="true"></entity-header>

    <mapas-container>

        <mapas-card class="feature">
            <template #title>
                <h3 class="card__title--title"><?php i::_e("Informações de Apresentação")?></h3>
                <p class="card__title--description"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>
                
                <div class="left">
                    <div class="row">
                        <div class="col-12">
                            <entity-cover :entity="entity"></entity-cover>
                        </div>
                    </div>    
                    
                    <div class="row v-center">
                        <div class="col-3 col-sm-12">
                            <entity-profile :entity="entity"></entity-profile>
                        </div>

                        <div class="col-9 col-sm-12">
                            <div class="rol">
                                <div class="col-12">
                                    <entity-field :entity="entity" label="Nome do espaço" prop="name"></entity-field>
                                </div>
                            </div>
                            <div class="rol">
                                <div class="col-12">
                                    <entity-field :entity="entity" label="Tipo do espaço" prop="type"></entity-field>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" prop="shortDescription"></entity-field>
                        </div>
                        <div class="col-12">
                            <entity-field :entity="entity" label="Link para página ou site do espaço" prop="site"></entity-field>
                        </div>
                    </div>                    
                </div>

                <div class="divider"></div>

                <div class="right">
                    <entity-terms :entity="entity" taxonomy="area" :editable="true" title="Área de atuação"></entity-terms>
                    <entity-social-media :entity="entity" :editable="true"></entity-social-media>
                </div>
                

            </template>
        </mapas-card>

        <main>         
            <mapas-card>
                <template #title>
                    <h3 ><?php i::_e("Endereço do espaço"); ?></h3>
                </template>
                <template #content>   
                    <div class="row">
                        <div class="col-5">
                            <entity-field :entity="entity" label="CEP" prop="En_CEP"></entity-field>
                        </div>
                        <div class="col-7">
                            <entity-field :entity="entity" label="Rua, avenida, travessa etc." prop="En_Nome_Logradouro"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3">
                            <entity-field :entity="entity" label="Número" prop="En_Num"></entity-field>

                        </div>
                        <div class="col-9">
                            <entity-field :entity="entity" label="Complemento" prop="En_Complemento"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" label="Bairro" prop="En_Bairro"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <entity-field :entity="entity" label="Município" prop="En_Municipio"></entity-field>
                        </div>
                        <div class="col-6">
                            <entity-field :entity="entity" label="Estado" prop="En_Estado"></entity-field>
                        </div>
                    </div>
                </template>   
            </mapas-card>

            <mapas-card>
                <template #title>
                    <h2><?php i::_e("Informações sobre o espaço"); ?></h2>                    
                </template>
                <template #content>   
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" prop="emailPublico"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" prop="emailPrivado"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" prop="telefonePublico"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" label="Telefone privado 1" prop="telefone1"></entity-field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-field :entity="entity" label="Telefone privado 2" prop="telefone2"></entity-field>
                        </div>
                    </div>
                </template>   
            </mapas-card>
            
            <mapas-card>
                <template #title>
                    <h2><?php i::_e("Mais informações públicas"); ?></h2>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="row">
                        <div class="col-12">
                            <entity-links title="Adicionar links" :entity="entity" :editable="true"></entity-links>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <entity-gallery-video title="<?php i::_e('Adicionar vídeos') ?>" :entity="entity" :editable="true"></entity-gallery-video>
                        </div>
                    </div>
                    <div class="row">
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
                    <div class="row">
                        <div class="col-12">
                            <entity-admins :entity="entity" :editable="true"></entity-admins>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <entity-terms :entity="entity" taxonomy="tag" title="Tags" :editable="true"></entity-terms>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <entity-related-agents :entity="entity" :editable="true"></entity-related-agents>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <entity-owner :entity="entity" title="Publicado por" :editable="true"></entity-owner>
                        </div>
                    </div>
                </template>
            </mapas-card>
        </aside>

    </mapas-container>

    <entity-actions :entity="entity" />

</div>