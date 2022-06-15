<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-terms entity-header popover field entity-header entity-actions');

?>

<entity-header :entity="entity" :editable="true"></entity-header>


<div class="card">
    <div class="card__title">
        <h3 class="card__title--title"><?php i::_e("Informações de Apresentação")?> </h3>
        <p class="card__title--description" ><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários")?></p>
    </div>

    <div class="card__content">
        <div class="row">
            <div class="card__content--name">
                <field :entity="entity" prop="name"></field>
            </div>
        </div>

    <div>
        <field :entity="entity" prop="shortDescription"></field>
    </div>
    
    </div>
</div>





<div>
    <button @click="entity.save()">salvar</button>
</div>
<div>
    <field :entity="entity" prop="name"></field>
</div>

<div>
    <field :entity="entity" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></field>
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
<!-- <div>
    <field :entity="entity" prop="largeDescription"></field>
</div> -->
<div>
    <field :entity="entity" prop="telefonePublico"></field>
</div>
<div>
    <field :entity="entity" prop="telefone1"></field>
</div>
<div>
    <field :entity="entity" prop="telefone2"></field>
</div>
<entity-terms :entity="entity" taxonomy="tag" title="Tags"></entity-terms>
<input v-model="entity.terms.tag.newTerm"> <button @click="entity.terms.tag.push(entity.terms.tag.newTerm)">add</button>
<entity-actions :entity="entity" />