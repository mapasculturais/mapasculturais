<?php

use MapasCulturais\i; ?>
<div ng-init="entityRelated = ['agent-owner-field', 'agent-collective-field', 'space-field']">
    <div ng-if="(entityRelated.includes(field.fieldType) && field.config.entityField =='shortDescription')">
        <textarea ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" maxlength="400"></textarea>
        <div>
            {{entity[fieldName].length ? entity[fieldName].length : 0}} / 400
        </div>
    </div>

    <div ng-if="!(entityRelated.includes(field.fieldType) && field.config.entityField =='shortDescription')">
        <textarea ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" maxlength="{{:: !field.maxSize ? '': field.maxSize }}"></textarea>
        <div ng-if="::field.maxSize">
            {{entity[fieldName].length ? entity[fieldName].length : 0}} / {{::field.maxSize}}
        </div>
    </div>

</div>