<label ng-repeat="option in ::field.fieldOptions">
    <input type="checkbox" checklist-model="entity[fieldName]" ng-click="saveField(field, entity[fieldName])" checklist-value="option"> {{::option}}
</label>
