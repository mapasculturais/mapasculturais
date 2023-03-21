<?php

use MapasCulturais\i;

?>

<label>
    <li class="evaluation-methods--item">
        <input type="radio" id="onlyAccountabilityPhase" name="accountability_phase" value="accountability" ng-change="data.step = 'accountability'" ng-model="newPhasePostData.evaluationMethod">
            <?php i::_e('Prestação de Contas'); ?>
        <p class="evaluation-methods--name">
            <?php i::_e('Assinale caso a oportunidade exija prestação de contas'); ?>
        </p>
    </li>
</label>