<?php

use MapasCulturais\i;
?>
<br><br>
<hr>

<h4><?php i::_e('Políticas Afirmativas') ?></h4>
<p><?php i::_e('Configure abaixo os percentuais de cada Política Afirmativa que serão aplicados na nota final, caso o proponente se enquadre. ') ?>
<section ng-if="data.isActiveAffirmativePolicies">
    <header>
        <div class="policy-roof">
            <p> <?php i::_e('Porcentagem total a ser aplicada') ?> </p>
            <input ng-model="data.affirmativePolicieRoof" ng-change="save()" type="number" step="0.01" value="0.00" min="0.00" max="100.00" placeholder="0,00" class="affirmative_policies-roof edit"> <span>%</span> <span class="detail"><?php i::_e('(0 = Sem limitações)') ?></span>
        </div>
    </header>

    <table>
        <tr>
            <th class="policy-field"><?php i::_e('Campo') ?></th>
            <th class="policy-value"><?php i::_e('Valor') ?></th>
            <th class="policy-percent"><?php i::_e('Porcentagem') ?></th>
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
                        <option value=""> Selecione </option>
                        <option value="true"> Sim </option>
                        <option value="false"> Não </option>
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
                <input ng-model="data.fieldsAffiermativePolicie[policy.id].fieldPercent" type="number" step="0.01" min="0.00" max="100.00" placeholder="0,00" value="{{policy.fieldPercent}}" class="affirmative_policies-roof edit"> <span>%</span>
            </td>

            <td>
                <button ng-click="removeSessionAffirmativePolice(policy)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
            </td>
        </tr>

    </table>
</section>

<button ng-click="activeAffirmativePolicies()" ng-if="!data.isActiveAffirmativePolicies" id="enableAffirmativePolicy" class="btn btn-default add"><?php i::_e('Utilizar Políticas Afirmativas') ?></button>
<button ng-click="activeAffirmativePolicies()" ng-if="data.isActiveAffirmativePolicies" id="disableAffirmativePolicy" class="btn btn-danger delete"><?php i::_e('Não Utilizar Políticas Afirmativas') ?></button>