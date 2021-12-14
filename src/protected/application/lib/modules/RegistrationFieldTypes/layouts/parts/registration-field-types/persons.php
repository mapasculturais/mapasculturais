<?php

use MapasCulturais\i; ?>

<div ng-class="field.error ? 'invalidField': '' " ng-if="::field.fieldType == 'persons'" id="field_{{::field.id}}">
    <span class="label icon">
        {{::field.title}}
        <div ng-if="requiredField(field)" class="field-required"><span class="description"><?php i::_e('obrigatório') ?></span><span class="icon-required">*</span></div>
    </span>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div ng-repeat="person in entity[fieldName]">
        <label ng-if="::field.config.name" style="display:inline-block">
            <?php i::_e('Nome') ?>: <br>
            <input ng-model="person.name" ng-blur="saveField(field, entity[fieldName])" required>
        </label>
        <label ng-if="::field.config.cpf" style="display:inline-block">
            <?php i::_e('CPF') ?>: <br>
            <input ng-model="person.cpf" ng-blur="saveField(field, entity[fieldName])" js-mask="999.999.999-99" placeholder="___.___.___-__" required>
        </label>
        <label ng-if="::field.config.function" style="display:inline-block">
            <?php i::_e('Função') ?>: <br>
            <input ng-model="person.function" ng-blur="saveField(field, entity[fieldName])" required>
        </label>
        <label ng-if="::field.config.relationship" style="display:inline-block">
            <?php i::_e('Parentesco') ?>: <br>
            <select ng-model="person.relationship" ng-blur="saveField(field, entity[fieldName])" ng-change="saveField(field, entity[fieldName])">
                <option value="1"><?php i::_e("Cônjuge ou Companheiro(a)") ?></option>
                <option value="2"><?php i::_e("Filho(a)") ?></option>
                <option value="3"><?php i::_e("Enteado(a)") ?></option>
                <option value="4"><?php i::_e("Neto (a) ou Bisneto (a)") ?></option>
                <option value="5"><?php i::_e("Pai ou Mãe") ?></option>
                <option value="6"><?php i::_e("Sogro(a)") ?></option>
                <option value="7"><?php i::_e("Irmão ou Irmã") ?></option>
                <option value="8"><?php i::_e("Genro ou Nora") ?></option>
                <option value="9"><?php i::_e("Outro Parente") ?></option>
                <option value="10"><?php i::_e("Não Parente") ?></option>
            </select>
        </label>

        <button ng-click="remove(entity[fieldName], $index); saveField(field, entity[fieldName], 0);" class="btn btn-danger"><?php i::_e('remover') ?></button>
    </div>

    <button ng-click="entity[fieldName] = entity[fieldName].concat([{}])" class="btn btn-primary"><?php i::_e('adicionar') ?></button>
</div>