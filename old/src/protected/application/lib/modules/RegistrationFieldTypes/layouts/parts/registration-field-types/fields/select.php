<select ng-required="requiredField(field)" ng-model="entity[fieldName]" ng-blur="saveField(field, entity[fieldName])" >
    <option ng-repeat="option in ::field.fieldOptions" value="{{::option.indexOf(':') >= 0 ? option.split(':')[0] : option}}">{{::option.indexOf(':') >= 0 ? option.split(':')[1] : option}}</option>
</select>