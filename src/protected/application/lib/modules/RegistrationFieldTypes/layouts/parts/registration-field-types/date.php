<div ng-if="field.fieldType === 'date'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <p>
        <?php $this->part('registration-field-types/fields/date') ?>
    </p>
</div>