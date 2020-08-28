<select ng-required="::field.required" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" >
    <option ng-repeat="option in ::field.fieldOptions">{{::option}}</option>
</select>