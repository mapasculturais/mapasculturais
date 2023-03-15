<?php

use MapasCulturais\i;
?>
<div ng-controller="QualificationEvaluationMethodConfigurationController" class="qualification-evaluation-configuration registration-fieldset">
    <div>
        <strong> <?php i::_e('Comprovante de endereço') ?> </strong>
        <select class="select-option"  name="data[obs]" ng-model="evaluation['obs']">
            <option>Sem comprovante de endereço</option>
            <option>qualquer coisa2</option>
        </select>
    </div>
    <br><br>
    <label>
        <strong class="textearea-strong"><?php i::_e('Observações') ?> </strong>
        <br>
            <textarea class="textearea-text" name="data[obs]" ng-model="evaluation['obs']"></textarea>
    </label>
    <br><br>
    <div>
        <label>
            <br>
            <strong> <?php i::_e('Status:inabilitado') ?> </strong>
        </label>
    </div>
</div>