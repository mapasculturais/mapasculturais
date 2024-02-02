<?php

use MapasCulturais\i;
$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$account_types = $definitions['payment_bank_account_type']['options'];
$bank_types = $definitions['payment_bank_number']['options'];
?>
<div>
    <div>
        <label>
            <br><strong><?= i::__('Típo de conta') ?></strong>
            <select ng-required="requiredField(field)" ng-model="entity[fieldName].account_type" ng-blur="saveField(field, entity[fieldName])">
                <option value=""><?= i::__('Selecione o tipo de conta') ?></option>
                <?php foreach($account_types as $key => $value): ?>
                    <option value="<?= $key?>"><?= $value ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </div>
    <div>
        <label>
            <br><strong><?= i::__('Número do banco') ?></strong>
            <select ng-required="requiredField(field)" ng-model="entity[fieldName].number" ng-blur="saveField(field, entity[fieldName])">
                <option value=""><?= i::__('Selecione o banco') ?></option>
                <?php foreach($bank_types as $key => $value): ?>
                    <option value="<?= $key?>"><?= $value ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </div>
    <div>
        <label>
            <br><strong><?= i::__('Agência') ?></strong>
            <input type="number" ng-required="requiredField(field)" ng-model="entity[fieldName].branch" ng-blur="saveField(field, entity[fieldName])" />
        </label>
    </div>
    <div>
        <label>
            <br><strong><?= i::__('Dígito verificador da agência') ?></strong>
            <input type="text" ng-required="requiredField(field)" ng-model="entity[fieldName].dv_branch" ng-blur="saveField(field, entity[fieldName])" maxlength="1" />
        </label>
    </div>
    <div>
        <label>
            <br><strong><?= i::__('Número da conta') ?></strong>
            <input type="number" ng-required="requiredField(field)" ng-model="entity[fieldName].account_number" ng-blur="saveField(field, entity[fieldName])" />
        </label>
    </div>
    <div>
        <label>
            <br><strong><?= i::__('Dígito verificador da conta') ?></strong>
            <input type="text" ng-required="requiredField(field)" ng-model="entity[fieldName].dv_account_number" ng-blur="saveField(field, entity[fieldName])" maxlength="1" />
        </label>
    </div>
</div>