<input ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" maxlength="{{:: !field.maxSize ?'': field.maxSize }}" js-mask="{{:: !field.mask ?'': field.mask }}" js-mask-options="{{:: !field.maskOptions ?'': field.maskOptions }}">
<div ng-if="::field.maxSize">
    {{entity[fieldName].length ? entity[fieldName].length : 0}} / {{::field.maxSize}}
</div>