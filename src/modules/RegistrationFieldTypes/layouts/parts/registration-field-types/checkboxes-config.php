<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'checkboxes'">
    <div class="options-selector">
        <label><?php i::_e('Opções do campo') ?></label><br>
        <textarea ng-model="field.fieldOptions" placeholder="<?php i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
        <label><?php i::_e('Limite de Opções') ?><input type="number" ng-model="field.config.maxOptions"></label><br>
        <div ng-if="countWords(field.fieldOptions) >= 10">
            <label><?php i::_e('Limite de Opções') ?><input type="number" ng-model="field.config.maxOptions"></label><br>
            <small class="registration-help"><?php i::_e('Digite o limite de opções. Deixe em branco ou coloque 0 para selecionar ilimitadas.'); ?></small>
        </div>
        <label><?php i::_e('Modo de visualização') ?></label>
        <select ng-model="field.config.viewMode">
            <option value="checkbox"><?php i::_e('Lista de checkboxes') ?></option>
            <option value="tag"><?php i::_e('Lista de tags') ?></option>
        </select>
    </div>
</div>