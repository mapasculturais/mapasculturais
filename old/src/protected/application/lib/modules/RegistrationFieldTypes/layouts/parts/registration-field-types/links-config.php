<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'links'"> 
    <label><input type="checkbox" ng-model="field.config.title" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir de título') ?></label><br>
</div>