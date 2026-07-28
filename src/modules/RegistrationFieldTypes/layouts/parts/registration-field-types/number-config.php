<?php
use MapasCulturais\i;
?>
<div ng-if="field.fieldType === 'number'" class="registration-fieldset"
     ng-init="field.config = (!field.config || typeof field.config !== 'object' || field.config.length !== undefined) ? {} : field.config; field.config.allowZero = (field.config.allowZero === false || field.config.allowZero === 0 || field.config.allowZero === '0' || field.config.allowZero === 'false') ? false : true">
    <p>
        <strong><?php i::_e('Opções do campo numérico'); ?></strong><br>
        <small><?php i::_e('Use estas opções para dizer como o sistema deve tratar o número zero e quantos dígitos o valor final pode ter (depois de salvo).'); ?></small>
    </p>

    <div style="margin: 1rem 0; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
        <label style="display:block; font-weight: bold; margin-bottom: 0.5rem;">
            <?php i::_e('O número zero (0) é uma resposta válida neste campo?'); ?>
        </label>
        <p style="margin: 0 0 0.75rem; color: #555;">
            <small>
                <?php i::_e('Selecione “Sim” quando zero for uma resposta aceitável (por exemplo, quantidade de público, redes ou apoiadores). Selecione “Não” quando zero não fizer sentido no contexto do campo (por exemplo, número de conta, agência, CEP ou PIS).'); ?>
            </small>
        </p>
        <label class="checkbox-label" style="display:inline-block; margin-right: 1.5rem;">
            <input type="radio" ng-model="field.config.allowZero" ng-value="true">
            <?php i::_e('Sim — aceitar 0'); ?>
        </label>
        <label class="checkbox-label" style="display:inline-block;">
            <input type="radio" ng-model="field.config.allowZero" ng-value="false">
            <?php i::_e('Não — rejeitar 0'); ?>
        </label>
    </div>

    <div style="margin: 1rem 0; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
        <label style="display:block; font-weight: bold; margin-bottom: 0.5rem;">
            <?php i::_e('Quantidade de dígitos do valor salvo'); ?>
        </label>
        <p style="margin: 0 0 0.75rem; color: #555;">
            <small>
                <?php i::_e('A validação considera o número já normalizado, sem zeros à esquerda. Exemplo: ao digitar 000963010, o sistema armazena 963010 (6 dígitos). Deixe em branco para não limitar; informe apenas o mínimo, apenas o máximo, ou ambos.'); ?>
            </small>
        </p>
        <label style="display:inline-block; margin-right: 1rem;">
            <?php i::_e('Mínimo de dígitos'); ?><br>
            <input type="number" min="1" step="1" ng-model="field.config.minDigits" placeholder="<?php i::esc_attr_e('Livre'); ?>">
        </label>
        <label style="display:inline-block;">
            <?php i::_e('Máximo de dígitos'); ?><br>
            <input type="number" min="1" step="1" ng-model="field.config.maxDigits" placeholder="<?php i::esc_attr_e('Livre'); ?>">
        </label>
    </div>
</div>
