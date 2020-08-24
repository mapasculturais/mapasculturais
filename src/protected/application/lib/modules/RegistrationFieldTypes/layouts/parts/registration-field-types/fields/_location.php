<?php 
use \MapasCulturais\i; 
?>
<div class="js-rfc-location field-location">
    <input ng-if="field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.latitude" class="js-rfc-input js-rfc-input-_lat"/>
    <input ng-if="field.config.setLatLon" type="hidden" ng-model="entity[fieldName].location.longitude" class="js-rfc-input js-rfc-input-_lon" />
    
    <input type="hidden" ng-model="entity[fieldName].endereco" class="js-rfc-input js-rfc-input-endereco" />
    {{entity[fieldName].endereco}}
    <p class="opportunity-field field-En_CEP">
        <label>
            <?= i::__('CEP') ?><br>
            <input ng-model="entity[fieldName].En_CEP" data-mask="99999-999" required class="js-rfc-input js-rfc-input-En_CEP" size=11 />
        </label>
    </p>
    <section>
        <p class="opportunity-field field-En_Nome_Logradouro">
            <label>
                <?= i::__('Logradouro') ?><br>
                <input ng-model="entity[fieldName].En_Nome_Logradouro" required="required" class="js-rfc-input js-rfc-input-En_Nome_Logradouro" />
            </label>
        </p>
        <p class="opportunity-field field-En_Num">
            <label>
                <?= i::__('Número') ?><br>
                <input ng-model="entity[fieldName].En_Num" class="js-rfc-input js-rfc-input-En_Num" />
            </label>
        </p>
        <p class="opportunity-field field-En_Complemento">
            <label>
                <?= i::__('Complemento') ?><br>
                <input ng-model="entity[fieldName].En_Complemento" class="js-rfc-input js-rfc-input-En_Complemento" />
            </label>
        </p>
    </section>
    <p class="opportunity-field field-En_Bairro">
        <label>
            <?= i::__('Bairro') ?><br>
            <input ng-model="entity[fieldName].En_Bairro" class="js-rfc-input js-rfc-input-En_Bairro" />
        </label>
    </p>
    <section>
        <p class="opportunity-field field-En_Estado">
            <label>
                <?= i::__('Estado') ?><br>
                <select ng-model="entity[fieldName].En_Estado" class="js-rfc-input js-rfc-input-En_Estado" >
                    <option value=""><?php i::_e("Selecione") ?></option>
                    <option ng-repeat="uf in ibge" value="{{uf.sigla}}">{{uf.nome}}</option>
                </select>
            </label>
        </p>
        <p class="opportunity-field field-En_Municipio">
            <label>
                <?= i::__('Cidade') ?><br>
                <select ng-model="entity[fieldName].En_Municipio" class="js-rfc-input js-rfc-input-En_Municipio" >
                    <option value=""><?php i::_e("Selecione") ?></option>
                    <option ng-if="entity[fieldName].En_Estado" ng-repeat="municipio in ibge[entity[fieldName].En_Estado].municipios" value="{{municipio.nome}}">{{municipio.nome}}</option>

                </select>
            </label>
        </p>
    </section>
    <p ng-if="field.config.setPrivacy" class="rfc-input rfc-En_">
        <label>
            <input ng-model="entity[fieldName].publicLocation" ng-true-value="'true'" ng-false-value="" type="checkbox" class="js-rfc-input js-rfc-input-publicLocation" />
            <?= i::__('Marque para deixar tornar sual localização pública.') ?><br>
        </label>
    </p>
</div>