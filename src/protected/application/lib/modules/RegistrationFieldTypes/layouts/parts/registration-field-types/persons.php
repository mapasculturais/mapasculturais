<?php use MapasCulturais\i; ?>
<div ng-if="::field.fieldType == 'persons'" id="field_{{::field.id}}" >
    <div class="label icon"> {{::field.title}} {{::field.required ? '*' : ''}}</div>
    
    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div ng-repeat="person in entity[fieldName]">
        <label ng-if="::field.config.name" style="display:inline-block">
            <?php i::_e('Nome') ?>: <br>
            <input ng-model="person.name" ng-blur="saveField(field, entity[fieldName])" required >
        </label>
        <label ng-if="::field.config.cpf" style="display:inline-block">
            <?php i::_e('CPF') ?>: <br>
            <input ng-model="person.cpf" ng-blur="saveField(field, entity[fieldName])" js-mask="999.999.999-99" placeholder="___.___.___-__" required >
        </label>
        <label ng-if="::field.config.relationship" style="display:inline-block">
            <?php i::_e('Parentesco') ?>: <br>
            <input ng-model="person.relationship" ng-blur="saveField(field, entity[fieldName])" required >
        </label>
        
        <button ng-click="remove(entity[fieldName], $index); saveField(field, entity[fieldName], 0);" class="btn btn-danger"><?php i::_e('remover') ?></button>
    </div>
    
    <button ng-click="entity[fieldName] = entity[fieldName].concat([{}])" class="btn btn-primary"><?php i::_e('adicionar') ?></button>
</div>