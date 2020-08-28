<div ng-if="::field.fieldType == 'section'" id="field_{{::field.id}}" >
    <div class="label"> {{::field.title}} </div>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
</div>