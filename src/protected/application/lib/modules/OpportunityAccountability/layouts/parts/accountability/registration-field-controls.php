<?php
use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('accountability-registration-field-controls', 'before') ?>
<div class='accountability-registration-field-controls' style="padding: 1em;">
    <?php $this->applyTemplateHook('accountability-registration-field-controls', 'begin') ?>
    <label>
        <input type="checkbox" ng-model="evaluationData.openFields[getFieldIdentifier(field)]">
        <?= i::__('Campo aberto para edição') ?>
    </label>

    <label>
        <input type="checkbox" ng-model="openChats[getFieldIdentifier(field)]" ng-change="toggleChat(field)" >
        <?= i::__('Conversação aberta') ?>
    </label>
    
    <?php $this->applyTemplateHook('accountability-registration-field-controls', 'end') ?>
</div>
<?php $this->applyTemplateHook('accountability-registration-field-controls', 'after') ?>