<div ng-if="field.fieldType == 'section'" id="registration-field-{{field.id}}" >
    <div class="label"> {{field.title}} </div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>