<?php

use MapasCulturais\i;

?>
<ul class="evaluation-methods">
    <li class="evaluation-methods--item">
        <input type="checkbox" id="hasAccountability" name="hasAccountability" ng-model="hasAccountability" ng-false-value="">
        <label for="hasAccountability"><?php i::_e("Haverá prestação de contas"); ?></label>
        <p class="evaluation-methods--name"><?php i::_e("Assinale caso a oportunidade exija prestação de contas"); ?></p>
    </li>
</ul>