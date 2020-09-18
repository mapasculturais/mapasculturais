<?php use MapasCulturais\i;?>

<div ng-if="field.fieldType !== 'section'">
    <label>
        <input type="checkbox" ng-model="field.required"> 
        <?php i::_e("O preenchimento deste campo é obrigatório");?>
    </label>
    
    <div ng-if="field.required && data.fields.length >= 1">
        <label>
            <input type="checkbox" ng-model="field.config.require.condition" ng-true-value="'1'" ng-false-value="">
            <?php i::_e("Condicionar a obrigatoriedade pelo valor de outro campo") ?>
        </label>
        <div ng-if="field.config.require.condition">
            <label style="display:block;">
                <?php i::_e('Campo') ?><br>
                <select ng-model="field.config.require.field">
                    <option></option>
                    <option ng-repeat="f in data.fields" ng-if="f != field" value="field_{{f.id}}">#{{f.id}} - {{f.title}}</option>
                </select>
            </label>
            <label style="display:block;">
                <?php i::_e('Valor') ?><br>
                <input ng-if="!field.options" type="text" ng-model="field.config.require.value">
            </label>
            <label style="display:block;">
            <input type="checkbox" ng-model="field.config.require.hide" ng-true-value="'1'" ng-false-value="">
                <?php i::_e('Ocultar o campo quando não for obrigatório'); ?>
            </label>
        </div>
    </div>
</div>