<?php
use MapasCulturais\i;
$this->import('entity-field entity-map');
?>


<div class="entity-location grid-12">
    <div class="entity-location__title col-12">
        <label ><?= i::__('Endereço')?></label>
    </div>

    <div class="col-12" v-if="editable">
        <div class="grid-12">
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

            <div class="col-6">
                <entity-field :entity="entity" prop="publicLocation"></entity-field>
            </div>
        </div>
    </div>

    <div class="col-12">
        <p class="entity-location__address">
            <span v-if="entity.En_Nome_Logradouro">{{entity.En_Nome_Logradouro}}, </span>
            <span v-if="entity.En_Num">{{entity.En_Num}}, </span>
            <span v-if="entity.En_Bairro">{{entity.En_Bairro}}. </span>
            <span v-if="entity.En_Municipio"> {{entity.En_Municipio}}/</span>
            <span v-if="entity.En_Estado">{{entity.En_Estado}} - </span>            
            <span v-if="entity.En_CEP">CEP: {{entity.En_CEP}}. </span>
        </p>
        <entity-map :entity="entity" :editable="true"></entity-map>
    </div>

</div>
