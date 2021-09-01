<?php
use \MapasCulturais\i;

$this->includeGeocodingAssets();
?>
<div class="js-rfc-location field-location">
    <input ng-if="::field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.latitude" class="js-rfc-input js-rfc-input-_lat"/>
    <input ng-if="::field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.longitude" class="js-rfc-input js-rfc-input-_lon" />
    
    <input type="hidden" ng-model="entity[fieldName].endereco" class="js-rfc-input js-rfc-input-endereco" />
    <p class="opportunity-field field-En_CEP">
        <label>
            <?= i::__('CEP') ?><br>
            <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_CEP" js-mask="99999-999"  ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_CEP" size=11 />
        </label>
    </p>
    <section>
        <p class="opportunity-field field-En_Nome_Logradouro">
            <label>
                <?= i::__('Logradouro') ?><br>
                <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Nome_Logradouro" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Nome_Logradouro" />
            </label>
        </p>
        <p class="opportunity-field field-En_Num">
            <label>
                <?= i::__('Número') ?><br>
                <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Num" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Num" />
            </label>
        </p>
        <p class="opportunity-field field-En_Complemento">
            <label>
                <?= i::__('Complemento') ?><br>
                <input ng-model="entity[fieldName].En_Complemento" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Complemento" />
            </label>
        </p>
    </section>
    <p class="opportunity-field field-En_Bairro">
        <label>
            <?= i::__('Bairro') ?><br>
            <input ng-required="requiredField(field)" ng-model="entity[fieldName].En_Bairro" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Bairro" />
        </label>
    </p>
    <section>
        <p class="opportunity-field field-En_Estado">
            <label>
                <?= i::__('Estado') ?><br>
                <select ng-required="requiredField(field)" ng-model="entity[fieldName].En_Estado" ng-blur="saveField(field, entity[fieldName])" ng-focus="saveField(field, entity[fieldName],10000)" class="js-rfc-input js-rfc-input-En_Estado" >
                    <option value=""><?php i::_e("Selecione") ?></option>
                    <option ng-repeat="uf in ::ibge" value="{{::uf.sigla}}">{{::uf.nome}}</option>
                </select>
            </label>
        </p>
        <p class="opportunity-field field-En_Municipio">
            <label>
                <?= i::__('Cidade') ?><br>
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