<div ng-if="::field.fieldType == 'section'" id="field_{{::field.id}}" class="title_section">
    <br>
    <div class="label"> {{::field.title}} </div>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>
</div>