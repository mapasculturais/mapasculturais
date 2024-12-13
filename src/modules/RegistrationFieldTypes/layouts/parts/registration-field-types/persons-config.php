<?php

use MapasCulturais\i; ?>

<div ng-if="field.fieldType === 'persons'">
    <label> <?= i::__('Nome do botão') ?> <br> <input type="text" ng-model="field.config.buttonText" placeholder="Nome do botão no formulário" /></label>
</div>

<p ng-if="field.fieldType === 'persons'">
    <small><?php i::_e("Selecione os campos solicitados"); ?>:</small><br>
    
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.name" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Nome') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.fullName" ng-true-value="'true'" ng-false-value=""> <?php i::_e('nome completo') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.socialName" ng-true-value="'true'" ng-false-value=""> <?php i::_e('nome social') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.cpf" ng-true-value="'true'" ng-false-value=""> <?php i::_e('CPF') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.income" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Renda') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.education" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Escolaridade') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.telephone" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Telefone do representante') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.email" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Email do representante') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.race" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Raça/Cor') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.gender" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Genero ') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.sexualOrientation" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Orientação sexual') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.deficiencies" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Informações sobre deficiencias') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.comunty" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pertencimento a povos ou comunidades tradicionais') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.area" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Áreas de atuação') ?></label>
    <label class="checkbox-label"><input type="checkbox" ng-model="field.config.funcao" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Funções/Profissões') ?></label>
</p>

<p ng-if="field.fieldType === 'persons'">
    <small><?php i::_e("Selecione os campos que serão obrigatórios"); ?>:</small><br>

    <label ng-if="field.config.name" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.name" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Nome') ?></label>
    <label ng-if="field.config.fullName" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.fullName" ng-true-value="'true'" ng-false-value=""> <?php i::_e('nome completo') ?></label>
    <label ng-if="field.config.socialName" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.socialName" ng-true-value="'true'" ng-false-value=""> <?php i::_e('nome social') ?></label>
    <label ng-if="field.config.cpf" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.cpf" ng-true-value="'true'" ng-false-value=""> <?php i::_e('CPF') ?></label>
    <label ng-if="field.config.income" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.income" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Renda') ?></label>
    <label ng-if="field.config.education" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.education" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Escolaridade') ?></label>
    <label ng-if="field.config.telephone" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.telephone" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Telefone do representante') ?></label>
    <label ng-if="field.config.email" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.email" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Email do representante') ?></label>
    <label ng-if="field.config.race" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.race" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Raça/Cor') ?></label>
    <label ng-if="field.config.gender" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.gender" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Genero ') ?></label>
    <label ng-if="field.config.sexualOrientation" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.sexualOrientation" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Orientação sexual') ?></label>
    <label ng-if="field.config.deficiencies" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.deficiencies" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Informações sobre deficiencias') ?></label>
    <label ng-if="field.config.comunty" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.comunty" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Pertencimento a povos ou comunidades tradicionais') ?></label>
    <label ng-if="field.config.area" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.area" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Áreas de atuação') ?></label>
    <label ng-if="field.config.funcao" class="checkbox-label"><input type="checkbox" ng-model="field.config.requiredFields.funcao" ng-true-value="'true'" ng-false-value=""> <?php i::_e('Funções/Profissões') ?></label>
</p>