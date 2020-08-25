<label ng-repeat="option in field.fieldOptions">
    <input ng-required="field.required" type="checkbox" checklist-model="entity[fieldName]" checklist-value="option"> {{option}}
</label>
