<?php
use MapasCulturais\i;
?>
<br><br>
<hr>

<h4><?php i::_e('Políticas afirmativas') ?></h4>
<p><?php i::_e('Configure abaixo as políticas afirmativas de avaliação técnica') ?>
<section>
    <!-- {{data.fieldsAffirmativePolicies}} -->

    <header>
        <p style="font-size: 12px; margin: 0; white-space: nowrap;"> <?php i::_e('Porcentagem máxima das políticas afirmativas') ?> </p>
        <input ng-model="data.fieldsAffiermativePolicie.roof" type="number" step="0.01" value="0.00" min="0.00" max="100.00" placeholder="0,00" class="affirmative_policies-roof edit"> <span>%</span> <!-- ng-change="save({sections: data.sections})" ng-model-options='{ debounce: data.debounce }' -->
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


        <tr ng-repeat="(key,policy) in data.sessionsAffirmativePolicies track by $index" id="{{policy.id}}">
            <!-- ng-if="!data.affirmativePolicies" id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id" -->
            <td class="policy-percent">
                <input ng-model="data.fieldsAffiermativePolicie.fieldPercent" type="number" step="0.01" value="{{policy.percentField}}" min="0.00" max="100.00" placeholder="<?php i::_e('informe a porcentagem do critério') ?>" class="affirmative_policies-roof edit"> <span>%</span>
                <!-- ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }' -->
            </td>

            <td class="policy-field">
                <select ng-model="data.fieldsAffiermativePolicie.field">
                    <option ng-repeat="field in data.registrationFieldConfigurations" value="{{field.id}}"> {{field.title}} </option>
                </select>
            </td>

            <td class="policy-value">
                <input ng-model="data.fieldsAffiermativePolicie.value" type="text">
            </td>

            <td>
                <button ng-click="removePolicy(policy)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
            </td>
        </tr>

    </table>
</section>
<button ng-click="activeAffirmativePolicies()" class="btn btn-default add"><?php i::_e('Ativar seção de políticas afirmativas') ?></button>