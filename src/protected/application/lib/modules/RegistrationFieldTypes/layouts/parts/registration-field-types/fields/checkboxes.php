<label ng-repeat="option in field.fieldOptions">
    <input type="checkbox" checklist-model="entity[fieldName]" checklist-value="option"> {{option}}
</label>
