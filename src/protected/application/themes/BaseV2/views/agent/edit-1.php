<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('
    mapas-field mapas-container mapas-card 
    entity-profile entity-cover entity-terms entity-admins
    entity-header entity-actions entity-owner entity-social-media
    entity-related-agents');
?>

<div class="main-app edit-1">
    
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
                        <mapas-field :entity="entity" prop="name"></mapas-field>
                    </div>
                    
                    <div class="row">
                        <mapas-field :entity="entity" prop="shortDescription"></mapas-field>
                    </div>  
                    <div class="row">
                        <mapas-field :entity="entity" prop="site"></mapas-field>
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
                        <mapas-field :entity="entity" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="documento"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="emailPrivado"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="telefonePublico"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="emailPublico"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="telefone1"></mapas-field>
                        <mapas-field :entity="entity" prop="telefone2"></mapas-field>
                    </div>
                    divider
                    <div class="row">
                        <mapas-field :entity="entity" prop="En_CEP"></mapas-field>
                        <mapas-field :entity="entity" prop="En_Nome_Logradouro"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="En_Num"></mapas-field>
                        <mapas-field :entity="entity" prop="En_Bairro"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="En_Complemento"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="En_Municipio"></mapas-field>
                        <mapas-field :entity="entity" prop="En_Estado"></mapas-field>
                    </div>
                    <div class="row">
                        <!-- <mapas-field :entity="entity" prop="publicLocation"></mapas-field> -->
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="longDescription"></mapas-field>
                    </div>
                </template>
            </mapas-card>

            <mapas-card>
                <template #title>
                    <h3><?php i::_e("Dados pessoais sensíveis"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="row">
                        <mapas-field :entity="entity" prop="dataDeNascimento" label="<?= i::__('Deta de Nascimento') ?>"></mapas-field>
                        <mapas-field :entity="entity" prop="genero"></mapas-field>
                    </div>
                    <div class="row">
                        <mapas-field :entity="entity" prop="orientacaoSexual"></mapas-field>
                        <mapas-field :entity="entity" prop="raca"></mapas-field>
                    </div>
                </template>
            </mapas-card>

            <mapas-card>
                <template #title>
                    <h3><?php i::_e("Mais informações públicas"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
            </mapas-card>
        </main>

        <aside>
            <mapas-card>
                <template #content>
                    
                    <div class="row">
                        <entity-terms :entity="entity" taxonomy="tag" title="Tags" :editable="true"></entity-terms>
                    </div>

                    <div class="row">
                        <entity-related-agents :entity="entity" :editable="true"></entity-related-agents>
                    </div>

                    <div class="row">
                        <entity-admins :entity="entity" :editable="true"></entity-admins>
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



<!-- 
<tabs>
    <tab label="label Tag" slug="tab1">
        Teste tag    
    </tab>
    <tab label="label Tag 2" slug="tab2">
        Teste tag 2 
    </tab>
</tabs> 

<div>
    <button @click="entity.save()"> salvar </button>
</div>
<input v-model="entity.terms.tag.newTerm"> <button @click="entity.terms.tag.push(entity.terms.tag.newTerm)"> add </button> 
-->