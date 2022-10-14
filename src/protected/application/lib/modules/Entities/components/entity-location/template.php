<?php
use MapasCulturais\i;
$this->import('entity-field entity-map');
?>


<div class="entity-location grid-12">
    <div v-if="!hideLabel" class="entity-location__title col-12">
        <label ><?= i::__('Endereço')?></label>
    </div>

    <div class="col-12" v-if="editable">
        <div class="grid-12">
            <div class="col-4 sm:col-12">
                <entity-field @change="address(); pesquisacep(entity.En_CEP);" :entity="entity" prop="En_CEP"></entity-field>
            </div>

            <div class="col-8 sm:col-12">
                <entity-field @change="address()" :entity="entity" prop="En_Nome_Logradouro"></entity-field>
            </div>                        

            <div class="col-2 sm:col-4">
                <entity-field @change="address()" :entity="entity" prop="En_Num"></entity-field>
            </div>

            <div class="col-10 sm:col-8">
                <entity-field @change="address()" :entity="entity" prop="En_Bairro"></entity-field>
            </div>

            <div class="col-12">
                <entity-field @change="address()" :entity="entity" prop="En_Complemento"></entity-field>
            </div>

            <div class="col-6 sm:col-12">
                <entity-field @change="address()" :entity="entity" prop="En_Municipio"></entity-field>
            </div>

            <div class="col-6 sm:col-12">
                <entity-field @change="address()" :entity="entity" prop="En_Estado"></entity-field>
            </div>

            <div v-if="hasPublicLocation" class="col-6">
                <entity-field @change="address()" :entity="entity" prop="publicLocation"></entity-field>
            </div>
        </div>
    </div>

    <div class="col-12">
        <p class="entity-location__address">
            <span v-if="entity.endereco">{{entity.endereco}}</span>
            <span v-else><?= i::_e("Sem Endereço"); ?></span>
        </p>
        <entity-map :entity="entity" :editable="editable"></entity-map>
    </div>

</div>
