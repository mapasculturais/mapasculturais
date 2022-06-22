<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-profile entity-cover entity-terms entity-header popover field entity-header entity-actions tabs main-menu container card entity-owner');
?>

<div class="main-app edit-1">
    
    <entity-header :entity="entity" :editable="true"></entity-header>

    <container class="edit-1__content">

        <card class="card-1">
            <template #title>
                <h3 class="card__title--title"><?php i::_e("Informações de Apresentação")?></h3>
                <p class="card__title--description"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
            </template>
            <template #content>
                
                <div class="card-1__left">

                    <div class="row">
                        <entity-cover></entity-cover>
                    </div>    
                    
                    <div class="row">
                        <entity-profile></entity-profile>
                        <field :entity="entity" prop="name"></field>
                    </div>
                    
                    <div class="row">
                        <field :entity="entity" prop="shortDescription"></field>
                    </div>  
                    <div class="row">
                        <field :entity="entity" prop="site"></field>
                    </div>
                    
                </div>

                <div class="card-1__divider"></div>

                <div class="card-1__right">
                    <entity-terms :entity="entity" taxonomy="area" :editable="true" title="Áreas de interesse"></entity-terms>
                    <field :entity="entity" prop="shortDescription"></field>
                </div>

            </template>
        </card>

        <main>
            <card>
                <template #title>
                    <h2><?php i::_e("Dados Pessoais"); ?></h2>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistema e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>                
                    <div class="row">
                        <field :entity="entity" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="documento"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="emailPrivado"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="telefonePublico"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="emailPublico"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="telefone1"></field>
                        <field :entity="entity" prop="telefone2"></field>
                    </div>
                    divider
                    <div class="row">
                        <field :entity="entity" prop="longDescription"></field>
                    </div>
                </template>
            </card>

            <card>
                <template #title>
                    <h3><?php i::_e("Dados pessoais sensíveis"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="row">
                        <field :entity="entity" prop="dataDeNascimento" label="<?= i::__('Deta de Nascimento') ?>"></field>
                        <field :entity="entity" prop="genero"></field>
                    </div>
                    <div class="row">
                        <field :entity="entity" prop="orientacaoSexual"></field>
                        <field :entity="entity" prop="raca"></field>
                    </div>
                </template>
            </card>

            <card>
                <template #title>
                    <h3><?php i::_e("Mais informações públicas"); ?></h3>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
            </card>
        </main>

        <aside>
            <card>
                <template #content>
                    
                    <div class="row">
                        <entity-terms :entity="entity" taxonomy="tag" title="Tags" :editable="true"></entity-terms>
                    </div>

                    <div class="row">
                        <entity-owner :entity="entity" title="Publicado por" :editable="true"></entity-owner>
                    </div>

                </template>
            </card>
        </aside>
        
    </container>    

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