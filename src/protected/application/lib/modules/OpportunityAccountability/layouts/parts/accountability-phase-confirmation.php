<?php

use MapasCulturais\i;

?>

<div ng-if="data.step == 'accountability'">
    <ul class="evaluation-methods">
        <label>
            <li class="evaluation-methods--item">
                <input type="radio" value="accountability" checked>
                <?php i::_e('Prestação de Contas'); ?>
                <p class="evaluation-methods--name">
                    <?php i::_e('Assinale caso a oportunidade exija prestação de contas'); ?>
                </p>
            </li>
        </label>
        <hr style="height:1px;border-width:0;color:gray;">        
    </ul>
</div>
