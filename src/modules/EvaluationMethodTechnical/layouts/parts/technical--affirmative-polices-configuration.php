<?php

use MapasCulturais\i;
?>
<br><br>
<hr>
<div class="affirmativePolices">
    <h4><?php i::_e('Bônus por pontuação') ?></h4>
    <p><?php i::_e('Configure abaixo os valores de cada bônus por pontuação que serão aplicados na nota final, caso o proponente se enquadre. ') ?>
    <section ng-if="data.isActivePointReward">
        <header>
            <div class="policy-type">
                <label>
                    <input type="radio" ng-model="data.bonusType" value="percentage" ng-change="save()">
                    <?php i::_e('Percentual') ?>
                </label>
                &nbsp;
                <label>
                    <input type="radio" ng-model="data.bonusType" value="fixed" ng-change="save()">
                    <?php i::_e('Ponto fixo') ?>
                </label>
            </div>
            <div class="policy-roof">
                <p ng-if="data.bonusType === 'percentage'"> <?php i::_e('Porcentagem total a ser aplicada') ?> </p>
                <p ng-if="data.bonusType === 'fixed'"> <?php i::_e('Bônus máximo a ser aplicado') ?> </p>
                <input ng-model="data.pointRewardRoof" ng-change="save()" type="number" step="0.01" value="0.00" min="0.00" placeholder="0,00" class="affirmative_policies-roof edit">
                <span ng-if="data.bonusType === 'percentage'">%</span>
                <span ng-if="data.bonusType === 'fixed'"><?php i::_e('pt(s)') ?></span>
                <span class="detail"><?php i::_e('(0 = Sem limitações)') ?></span>
            </div>
        </header>

        <table>
            <tr>
                <th class="policy-field"><?php i::_e('Campo') ?></th>
                <th class="policy-value"><?php i::_e('Valor') ?></th>
                <th class="policy-percent" ng-if="data.bonusType === 'percentage'"><?php i::_e('Porcentagem') ?></th>
                <th class="policy-percent" ng-if="data.bonusType === 'fixed'"><?php i::_e('Pontos') ?></th>
                <th>
                    <button ng-click="addSessionAffirmativePolice()" class="btn btn-default add" title="<?php i::_e('Adicionar critério') ?>"></button>
                </th>
            </tr>

            <tr ng-repeat="(key,policy) in data.criteriaAffirmativePolicies track by $index" id="{{policy.id}}">
                <td class="policy-field">
                    <select ng-model="data.fieldsAffiermativePolicie[policy.id].field" ng-change="changeField(policy); ">
                        <option ng-repeat="field in data.registrationFieldConfigurations" value="{{field.id}}">#{{field.id}} - {{field.title}} </option>
                    </select>
                </td>

                <td class="policy-value">

                    <div ng-if="policy.viewDataValues == 'bool' || policy.viewDataValues == null">
                        <select ng-model="data.fieldsAffiermativePolicie[policy.id].value">
                            <option value=""> <?= i::_e('Selecione') ?> </option>
                            <option value="true"> <?= i::_e('Sim') ?> </option>
                            <option value="false"> <?= i::_e('Não') ?> </option>
                        </select>
                    </div>

                    <div class="check" ng-if="policy.viewDataValues == 'checkbox'">
                        <span ng-repeat="(key, v) in policy.valuesList">
                            <label>
                                <input type="checkbox" ng-model="data.fieldsAffiermativePolicie[policy.id].value[v]">
                                {{v}}
                            </label>
                        </span>
                    </div>
                </td>

                <td class="policy-percent">
                    <input ng-model="data.fieldsAffiermativePolicie[policy.id].bonusValue" type="number" step="0.01" min="0.00" ng-attr-max="data.bonusType === 'percentage' ? 100 : undefined" placeholder="0,00" class="affirmative_policies-roof edit">
                    <span ng-if="data.bonusType === 'percentage'">%</span>
                    <span ng-if="data.bonusType === 'fixed'"><?php i::_e('pt(s)') ?></span>
                </td>

                <td>
                    <button ng-click="removeSessionAffirmativePolice(policy)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
            </tr>

        </table>
    </section>

    <button ng-click="activeAffirmativePolicies()" ng-if="!data.isActivePointReward" id="enableAffirmativePolicy" class="btn btn-default add"><?php i::_e('Utilizar Bônus por pontuação') ?></button>
    <button ng-click="activeAffirmativePolicies()" ng-if="data.isActivePointReward" id="disableAffirmativePolicy" class="btn btn-danger delete"><?php i::_e('Não Utilizar Bônus por pontuação') ?></button>
</div>
