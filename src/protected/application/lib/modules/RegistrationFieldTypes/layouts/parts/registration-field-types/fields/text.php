<input ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" maxlength="{{:: !field.maxSize ?'': field.maxSize }}">
<div ng-if="::field.maxSize">
    {{entity[fieldName].length ? entity[fieldName].length : 0}} / {{::field.maxSize}}
</div>