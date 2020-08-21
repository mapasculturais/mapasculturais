<?php use MapasCulturais\i; ?>
<textarea ng-model="entity[fieldName]" maxlength='{{ !field.maxSize ?'': field.maxSize }}'></textarea>
<div ng-if="field.maxSize">
    <?php i::_e('Número de caracteres') ?>:
    {{entity[fieldName].length}} / {{field.maxSize}}
</div>