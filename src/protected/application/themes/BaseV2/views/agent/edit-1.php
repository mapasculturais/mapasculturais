<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('entity-terms field');
?>
<div>
    <button @click="entity.save()">salvar</button>
</div>
<div>
    <field :entity="entity" prop="name"></field>
</div>
<div>
    <field :entity="entity" prop="shortDescription"></field>
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
<div>
    <field :entity="entity" prop="largeDescription"></field>
</div>
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