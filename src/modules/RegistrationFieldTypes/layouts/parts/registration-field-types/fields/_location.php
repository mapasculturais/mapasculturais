<?php
use \MapasCulturais\i;

$this->includeGeocodingAssets();
?>
<div class="js-rfc-location field-location">
    <input ng-if="::field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.latitude" class="js-rfc-input js-rfc-input-_lat"/>
    <input ng-if="::field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.longitude" class="js-rfc-input js-rfc-input-_lon" />
    
    <input type="hidden" ng-model="entity[fieldName].endereco" class="js-rfc-input js-rfc-input-endereco" />
    <div ng-if="lockedEntityField('En_CEP')"><strong><?= i::__('CEP') ?></strong>: {{entity[fieldName].En_CEP}}</div>
    <p ng-if="!lockedEntityField('En_CEP')" class="opportunity-field field-En_CEP">
        <label>
            <strong><?= i::__('CEP') ?></strong><br>
            <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_CEP" js-mask="99999-999"  ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_CEP" size=11 />
        </label>
    </p>
    <section>
    <div ng-if="lockedEntityField('En_Nome_Logradouro')"><strong><?= i::__('Logradouro') ?></strong>: {{entity[fieldName].En_Nome_Logradouro}}</div>    
    <p ng-if="!lockedEntityField('En_Nome_Logradouro')" class="opportunity-field field-En_Nome_Logradouro">
            <label>
                <strong><?= i::__('Logradouro') ?></strong><br>
                <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Nome_Logradouro" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Nome_Logradouro" />
            </label>
        </p>
        <div ng-if="lockedEntityField('En_Num')"><strong><?= i::__('Número') ?></strong>: {{entity[fieldName].En_Num}}</div>
        <p ng-if="!lockedEntityField('En_Num')" class="opportunity-field field-En_Num">
            <label>
                <strong><?= i::__('Número') ?></strong><br>
                <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Num" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Num" />
            </label>
        </p>
        <div ng-if="lockedEntityField('En_Complemento')"><strong><?= i::__('Complemento') ?></strong>: {{entity[fieldName].En_Complemento}}</div>
        <p ng-if="!lockedEntityField('En_Complemento')" class="opportunity-field field-En_Complemento">
            <label>
                <strong><?= i::__('Complemento') ?></strong><br>
                <input ng-model="entity[fieldName].En_Complemento" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Complemento" />
            </label>
        </p>
    </section>
    <div ng-if="lockedEntityField('En_Bairro')"><strong><?= i::__('Bairro') ?></strong>: {{entity[fieldName].En_Bairro}}</div>
    <p ng-if="!lockedEntityField('En_Bairro')" class="opportunity-field field-En_Bairro">
        <label>
            <strong><?= i::__('Bairro') ?></strong><br>
            <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Bairro" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Bairro" />
        </label>
    </p>
    <section>
    <div ng-if="lockedEntityField('En_Estado')"><strong><?= i::__('Estado') ?></strong>: {{entity[fieldName].En_Estado}}</div>    
    <p ng-if="!lockedEntityField('En_Estado')" class="opportunity-field field-En_Estado">
            <label>
                <strong><?= i::__('Estado') ?></strong><br>
                <select ng-required="requiredField(field)" ng-model="entity[fieldName].En_Estado" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Estado" >
                    <option value=""><?php i::_e("Selecione") ?></option>
                    <option ng-repeat="uf in ::ibge" value="{{::uf.sigla}}">{{::uf.nome}}</option>
                </select>
            </label>
        </p>
        <div ng-if="lockedEntityField('En_Municipio')"><strong><?= i::__('Cidade') ?></strong>: {{entity[fieldName].En_Municipio}}</div>
        <p ng-if="!lockedEntityField('En_Municipio')" class="opportunity-field field-En_Municipio">
            <label>
                <strong><?= i::__('Cidade') ?></strong><br>
                <select ng-required="requiredField(field)" ng-model="entity[fieldName].En_Municipio" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Municipio" >
                    <option value=""><?php i::_e("Selecione") ?></option>
                    <option ng-if="entity[fieldName].En_Estado" ng-repeat="municipio in ibge[entity[fieldName].En_Estado].municipios" value="{{::municipio.nome}}">{{::municipio.nome}}</option>

                </select>
            </label>
        </p>
    </section>
    <p ng-if="::field.config.setPrivacy" class="rfc-input rfc-En_">
        <label>
            <input ng-model="entity[fieldName].publicLocation" ng-true-value="'true'" ng-false-value="" type="checkbox" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-publicLocation" />
            <?= i::__('Marque para deixar tornar sua localização pública.') ?><br>
        </label>
    </p>
</div>