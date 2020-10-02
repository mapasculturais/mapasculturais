<?php use MapasCulturais\i; ?>
<div ng-repeat="link in entity[fieldName]">
    <label ng-if="::field.config.title" style="display:inline-block">
        <?php i::_e('Título') ?>: <br>
        <input ng-model="link.title" ng-blur="saveField(field, entity[fieldName])" >
    </label>
    <label style="display:inline-block">
        <?php i::_e('URL') ?>: <br>
        <input ng-model="link.value" ng-blur="saveField(field, entity[fieldName])" placeholder="https://" required >
    </label>

    <button ng-click="remove(entity[fieldName], $index); saveField(field, entity[fieldName], 0);" class="btn btn-danger"><?php i::_e('remover') ?></button>
</div>
{{ (entity[fieldName] = (entity[fieldName] ? entity[fieldName] : []) ) && false ? '' : ''}}
<button ng-click="entity[fieldName] = entity[fieldName].concat([{}])" class="btn btn-primary"><?php i::_e('adicionar') ?></button>
