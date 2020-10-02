<label ng-repeat="option in ::field.fieldOptions">
    <input type="checkbox" checklist-model="entity[fieldName]" ng-click="saveField(field, entity[fieldName])" checklist-value="option.indexOf(':') >= 0 ? option.split(':')[0] : option"> {{::option.indexOf(':') >= 0 ? option.split(':')[1] : option}}
</label>