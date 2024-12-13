<?php use MapasCulturais\i; ?>
<div ng-if="field.fieldType === 'addresses'">
    <label> <?= i::__('Nome do botão') ?> <br> <input type="text" ng-model="field.config.buttonText" placeholder="Nome do botão no formulário" /></label>
</div>
<div ng-if="field.fieldType === 'addresses'">
    <!-- <label><input type="checkbox" ng-model="field.config.name" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir informação de nome') ?></label><br>
    <label><input type="checkbox" ng-model="field.config.cpf" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir informação de cpf') ?></label><br>
    <label><input type="checkbox" ng-model="field.config.relationship" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pedir informação de parentesco') ?></label><br>
    <label><input type="checkbox" ng-model="field.config.function" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Função') ?></label><br> -->
</div>