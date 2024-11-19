<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'select'">
    <div class="options-selector">
        <label><?php i::_e('Opções do campo') ?></label><br>
        <textarea ng-model="field.fieldOptions" placeholder="<?php i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
        <p class="registration-help"><?php i::_e("Informe uma opção por linha.");?></p>
    </div>
    <label><?php i::_e('Modo de visualização') ?></label>
    <select ng-model="field.config.viewMode">
        <option value="select"><?php i::_e('Select') ?></option>
        <option value="radio"><?php i::_e('Radio') ?></option>
    </select>
</div>