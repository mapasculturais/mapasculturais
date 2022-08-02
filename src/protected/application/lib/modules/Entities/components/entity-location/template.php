<?php
use MapasCulturais\i;
$this->import('entity-field');
?>


<div class="teste grid-12">
    <div class="col-4 sm:col-12">
        <label ><?= i::__('Endereço')?></label>
    </div>
    
    <div class="col-4 sm:col-12">
        <entity-field :entity="entity" prop="En_CEP"></entity-field>
    </div>

    <div class="col-8 sm:col-12">
        <entity-field :entity="entity" prop="En_Nome_Logradouro"></entity-field>
    </div>                        

    <div class="col-2 sm:col-4">
        <entity-field :entity="entity" prop="En_Num"></entity-field>
    </div>

    <div class="col-10 sm:col-8">
        <entity-field :entity="entity" prop="En_Bairro"></entity-field>
    </div>

    <div class="col-12">
        <entity-field :entity="entity" prop="En_Complemento"></entity-field>
    </div>

    <div class="col-6 sm:col-12">
        <entity-field :entity="entity" prop="En_Municipio"></entity-field>
    </div>

    <div class="col-6 sm:col-12">
        <entity-field :entity="entity" prop="En_Estado"></entity-field>
    </div>

    <!--    <entity-field :entity="entity" prop="publicLocation"></entity-field> --> -->

    <div class="col-12">
        <entity-field :entity="entity" prop="longDescription"></entity-field>
    </div>

    <div class="col-12">
        <entity-map :entity="entity" :editable="true"></entity-map>
    </div>

</div>
