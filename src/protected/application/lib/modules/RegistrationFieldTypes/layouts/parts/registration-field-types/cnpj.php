<div ng-if="::field.fieldType === 'cnpj'" id="field_{{::field.id}}">
    <div class="label"> {{::field.title}} {{::field.required ? '*' : ''}}</div>

    <div ng-if="::field.description" class="attachment-description">{{::field.description}}</div>

    <div><?php $this->part('registration-field-types/fields/cnpj') ?></div>
</div>