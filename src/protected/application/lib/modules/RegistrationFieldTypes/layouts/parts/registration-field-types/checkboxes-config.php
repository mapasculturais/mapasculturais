<div ng-if="field.fieldType === 'checkboxes'">
    <div class="options-selector">
        <label><?php \MapasCulturais\i::_e('Opções do campo') ?></label><br>
        <textarea ng-model="field.fieldOptions" placeholder="<?php \MapasCulturais\i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
        <p class="registration-help"><?php \MapasCulturais\i::_e("Informe uma opção por linha.");?></p>
    </div>
</div>