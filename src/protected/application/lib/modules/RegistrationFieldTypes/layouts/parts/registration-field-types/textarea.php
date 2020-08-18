<div ng-if="field.fieldType === 'textarea'" id="registration-field-{{field.id}}">
    <span class="label"> {{field.title}} {{field.required ? '*' : ''}}</span>
    <span>
        <?php $this->part('registration-field-types/fields/textarea') ?>
    </span>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
</div>