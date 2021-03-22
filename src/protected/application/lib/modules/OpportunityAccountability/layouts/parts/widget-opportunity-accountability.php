<?php

use MapasCulturais\i;

?>
<ul class="evaluation-methods">
    <label ng-class="{disable:!newPhasePostData.isLastPhase}">
    <li class="evaluation-methods--item">
        <input type="checkbox" id="hasAccountability" name="hasAccountability" ng-model="newPhasePostData.hasAccountability" ng-disabled="!newPhasePostData.isLastPhase" ng-false-value="">
        <?php i::_e("Haverá prestação de contas"); ?>
        <p class="evaluation-methods--name"><?php i::_e("Assinale caso a oportunidade exija prestação de contas"); ?></p>
    </li>
    </label>
</ul>