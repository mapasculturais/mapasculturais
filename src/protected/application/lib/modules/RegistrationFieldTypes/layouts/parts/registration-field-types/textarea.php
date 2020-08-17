<div ng-if="field.fieldType === 'textarea'" id="registration-field-{{field.id}}">
    <div class="label"> {{field.title}} {{field.required ? '*' : ''}}</div>

    <div ng-if="field.description" class="attachment-description">{{field.description}}</div>
    <p style="position: relative;">
        <?php $this->part('registration-field-types/fields/textarea') ?>
    </p>
</div>