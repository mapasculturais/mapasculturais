<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'select'">
    <div class="mc-content options-selector">
        <label>
            <?php i::_e('Opções do campo') ?><br>
            <textarea ng-model="field.fieldOptions" placeholder="<?php i::esc_attr_e("Opções de seleção");?>" style="min-height: 75px"/></textarea>
            <small class="registration-help"><?php i::_e("Informe uma opção por linha.");?></small>
        </label>
        <label>
            <?php i::_e('Modo de visualização') ?><br>
            <select ng-model="field.config.viewMode">
                <option value="select"><?php i::_e('Caixa de seleção') ?></option>
                <option value="radio"><?php i::_e('Lista de botões de rádio') ?></option>
            </select>
        </label>
    </div>
</div>