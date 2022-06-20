<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-terms entity-header popover field entity-header entity-actions tabs main-menu container card entity-owner');
?>

<entity-header :entity="entity" :editable="true"></entity-header>

<container class="edit-1">

    <card class="card-1">
        <template #title>
            <h3 class="card__title--title"> <?php i::_e("Informações de Apresentação")?> </h3>
            <p class="card__title--description"> <?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?> </p>
        </template>
        <template #content>
            
            <div class="card-1__left">

                <div class="row">
                    <div class="profileImg">
                        <div class="profileImg__img">
                            <iconify icon="bi:image-fill" />
                            <!-- <img href="" class="select-profileImg__img--img" /> -->
                        </div>
                        <label> <?php i::_e("Selecionar imagem de perfil"); ?> </label>
                    </div>
                    <field :entity="entity" prop="name"></field>
                </div>

                <div class="row">
                    <field :entity="entity" prop="shortDescription"></field>
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
                <h3> <?php i::_e("Dados Pessoais"); ?> </h3>
                <p> <?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistema e não serão exibidos publicamente"); ?> </p>
            </template>
            <template #content>                
                <div class="row">
                    <field :entity="entity" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></field>
                </div>
                <div class="row">
                    <field :entity="entity" prop="telefonePublico"></field>
                </div>
                <div class="row">
                    <field :entity="entity" prop="telefone1"></field>
                    <field :entity="entity" prop="telefone2"></field>
                </div>
            </template>
        </card>

        <card>
            <template #title>

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

    <entity-actions :entity="entity" />

</container>    



<!-- <tabs>
    <tab label="label Tag" slug="tab1">
        Teste tag    
    </tab>
    <tab label="label Tag 2" slug="tab2">
        Teste tag 2 
    </tab>
</tabs> -->




<div>
    <button @click="entity.save()">salvar</button>
</div>


<div>
    <field :entity="entity" prop="dataDeNascimento" label="<?= i::__('Deta de Nascimento') ?>"></field>
</div>
<div>
    <field :entity="entity" prop="raca"></field>
</div>
<div>
    <field :entity="entity" prop="genero"></field>
</div>
<div>
    <field :entity="entity" prop="longDescription"></field>
</div>



<input v-model="entity.terms.tag.newTerm"> <button @click="entity.terms.tag.push(entity.terms.tag.newTerm)">add</button>