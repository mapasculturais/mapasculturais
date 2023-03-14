<?php

use MapasCulturais\i;


?>
<div ng-controller="QualificationEvaluationMethodConfigurationController" class="technical-evaluation-form">
    {{data.criteria}}
    <div>
        <strong> <?php i::_e('Comprovante de endereço') ?> </strong>
        <select style="width:300px" name="data[obs]" ng-model="evaluation['obs']">
            <option>Sem comprovante de endereço</option>
            <option>qualquer coisa2</option>
        </select>
    </div>

    <label>
        <strong><?php i::_e('Observações') ?> <strong>
                <textarea name="data[obs]" ng-model="evaluation['obs']"></textarea>
    </label>
    <br><br>
    <div>
        <label>
            <strong> <?php i::_e('Status:inabilitado') ?> </strong>
        </label>
    </div>
</div>