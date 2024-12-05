<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'checkboxes'">
    <div class="mc-content options-selector">
        <label>
            <?php i::_e('Opções do campo') ?><br>
            <textarea ng-model="field.fieldOptions" placeholder="<?php i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
        </label>
        <label>
            <?php i::_e('Limite de Opções') ?><br>
            <input type="number" ng-model="field.config.maxOptions">
            <small class="registration-help"><?php i::_e('Digite o limite de opções. Deixe em branco ou coloque 0 para selecionar ilimitadas.'); ?></small>
        </label>
        <label>
            <?php i::_e('Modo de visualização') ?><br>
            <select ng-model="field.config.viewMode">
                <option value="checkbox"><?php i::_e('Lista de checkboxes') ?></option>
                <option value="tag"><?php i::_e('Lista de tags') ?></option>
            </select>
        </label>
    </div>
</div>