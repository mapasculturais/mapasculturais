<?php use MapasCulturais\i;?>

<div ng-if="field.fieldType !== 'section'">
    <label class="checkbox-label">
        <input type="checkbox" ng-model="field.required"> 
            <?php i::_e("Marca campo como obrigatório") ?>
    </label>
    <div ng-if="data.fields.length >= 1">
        <label class="checkbox-label">
            <input type="checkbox" ng-model="field.conditional">
            <?php i::_e("Condicionar a outro campo") ?>
        </label>
            <p><?php i::_e('Marque se deseja que este campo dependa da resposta de um outro campo do formulário. Por exemplo:outras opções')?></p>
        <div ng-if="field.conditional">
            <div class="edit-input">
                
            <label class="checkbox-label" style="display:block;">
                <?php i::_e('Campo Relacionado') ?><br>
                <select ng-model="field.conditionalField">
                    <option></option>
                    <option ng-repeat="f in data.fields" ng-if="f != field && allowedFieldCondition(f)" value="field_{{f.id}}">#{{f.id}} - {{f.title}}</option>
                </select>
            </label>
            <label class="checkbox-label" style="display:block;">
                <?php i::_e('Campo Condicionado') ?><br>
                <input ng-if="!field.options" type="text" ng-model="field.conditionalValue">
            </label>
            </div>
        </div>
    </div>
</div>