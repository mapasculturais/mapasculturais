<?php

use MapasCulturais\i;
?>
<br><br>
<hr>

<h4><?php i::_e('Políticas afirmativas') ?></h4>
<p><?php i::_e('Configure abaixo as políticas afirmativas de avaliação técnica') ?>
<section>
    <header>
        <p style="font-size: 12px; margin: 0; white-space: nowrap;"> <?php i::_e('Porcentagem máxima das políticas afirmativas') ?> </p>
        <input ng-model="data.fieldsAffiermativePolicie.roof" type="number" step="0.01" value="0.00" min="0.00" max="100.00" placeholder="0,00" class="affirmative_policies-roof edit"> <span>%</span>
    </header>

    <table>
        <tr>
            <th class="policy-percent"><?php i::_e('Porcentagem') ?></th>
            <th class="policy-field"><?php i::_e('Campo') ?></th>
            <th class="policy-value"><?php i::_e('Valor') ?></th>
            <th>
                <button ng-click="addSessionAffirmativePolice()" class="btn btn-default add" title="<?php i::_e('Adicionar critério') ?>"></button>
            </th>
        </tr>

        <tr ng-repeat="(key,policy) in data.criteriaAffirmativePolicies track by $index" id="{{policy.id}}">
            <td class="policy-percent">
                <input ng-model="data.fieldsAffiermativePolicie[policy.id].fieldPercent" type="number" step="0.01" min="0.00" max="100.00" placeholder="<?php i::_e('informe a porcentagem do critério') ?>" class="affirmative_policies-roof edit"> <span>%</span>
            </td>

            <td class="policy-field">
                <select ng-model="data.fieldsAffiermativePolicie[policy.id].field">
                    <option ng-repeat="field in data.registrationFieldConfigurations" value="{{field.id}}"> {{field.title}} </option>
                </select>
            </td>

            <td class="policy-value">
                <input ng-model="data.fieldsAffiermativePolicie[policy.id].value" type="text">
            </td>

            <td>
                <button ng-click="removeSessionAffirmativePolice(policy)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
            </td>
        </tr>

    </table>
</section>
<button ng-click="activeAffirmativePolicies()" class="btn btn-default add"><?php i::_e('Ativar seção de políticas afirmativas') ?></button>