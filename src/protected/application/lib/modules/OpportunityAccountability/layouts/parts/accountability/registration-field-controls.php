<?php
use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('accountability-registration-field-controls', 'before') ?>
<div class='accountability-registration-field-controls'>
    <?php $this->applyTemplateHook('accountability-registration-field-controls', 'begin') ?>
    <label>
        <?= i::__('Abrir campo para edição') ?>
        <span class="switch">
            <input type="checkbox" ng-model="openFields[getFieldIdentifier(field)]" ng-change="toggleOpen(field)" >
            <span class="slider"></span>
        </span>
    </label><br>

    <label>
        <?= i::__('Abrir conversação com proponente') ?>
        <span class="switch">
            <input type="checkbox" ng-model="openChats[getFieldIdentifier(field)]" ng-change="toggleChat(field)" >
            <span class="slider"></span>
        </span>
    </label>

    <?php $this->applyTemplateHook('accountability-registration-field-controls', 'end') ?>
</div>
<?php $this->applyTemplateHook('accountability-registration-field-controls', 'after') ?>