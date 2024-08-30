<?php

use MapasCulturais\i;

 if($opportunity->registrationRanges): ?>
    <div class="registration-fieldset">
        <div id="category">
            
            <?php if($opportunity->isOpportunityPhase && $entity->preview): ?>
                <div class="alert info">
                    <?= i::__("O campo categoria/opções não será exibido para o usuário final. Está neste formulário somente para auxiliar nos testes dos campos condicionados.");?>
                </div>
            <?php endif?>
            
            <div class="attachment">
                <!-- TODO: ng-required="requiredField(field)" -->
                <!-- foi trocado ng-blur para ng-change, para dar o trigger na função sempre que uma nova opção no select for escolhida -->
                <label><?= i::__('Faixas / Linhas') ?></label>
                <select ng-model="entity.range" ng-change="saveField({fieldName:'range'}, entity.range)" >
                    <option ng-repeat="option in registrationRanges" value="{{option.label}}">{{option.label}}</option>
                </select>
            </div>
        </div>
        <!-- <div ng-repeat="error in data.errors.category" class="alert danger">{{error}}</div> -->
    </div>
    
<?php endif; ?>