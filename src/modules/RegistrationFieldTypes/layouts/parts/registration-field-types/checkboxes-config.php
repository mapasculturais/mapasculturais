<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'checkboxes'">
    <div class="options-selector">
        <label><?php \MapasCulturais\i::_e('Opções do campo') ?></label><br>
        <textarea ng-model="field.fieldOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
        <label><?php i::_e('Super teste') ?><input type="number" ng-model="field.config.maxOptions"> </label><br>
        <p class="registration-help"><?php \MapasCulturais\i::_e("Informe uma opção por linha.");?></p>
    </div>
</div>