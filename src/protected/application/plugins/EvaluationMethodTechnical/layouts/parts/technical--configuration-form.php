<?php
use MapasCulturais\i;
?>
<div ng-controller="TechnicalEvaluationMethodConfigurationController" class="technical-evaluation-configuration registration-fieldset">
    <h4><?php i::_e('Critérios') ?></h4>
    <p><?php i::_e('Configure abaixo os critérios de avaliação técnica') ?>
    <section id="{{section.id}}" ng-repeat="section in data.sections">
        <header>
            <input ng-model="section.name" placeholder="<?php i::_e('informe o nome da seção') ?>" class="section-name edit" ng-change="save({sections: data.sections})" ng-model-options='{ debounce: data.debounce }'>
            <button ng-if="section.name.trim().length > 0" ng-click="deleteSection(section)" class="btn btn-danger delete alignright"><?php i::_e('Remover seção') ?></button>
            <button ng-if="section.name.trim().length == 0" ng-click="deleteSection(section)" class="btn btn-default delete alignright"><?php i::_e('Cancelar') ?></button>

        </header>

        <table>
            <tr>
                <th class="criterion-title"><?php i::_e('Título do critério') ?></th>
                <th class="criterion-num"><?php i::_e('Mínimo') ?></th>
                <th class="criterion-num"><?php i::_e('Máximo') ?></th>
                <th class="criterion-num"><?php i::_e('Peso') ?></th>
                <th>
                    <button ng-click="addCriterion(section)" class="btn btn-default add" title="<?php i::_e('Adicionar critério') ?>"></button>
                </th>
            </tr>

            <tr id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id">
                <td class="criterion-title"><input ng-model="cri.title" placeholder="<?php i::_e('informe o título do critério') ?>" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.min" type="number" placeholder="<?php i::_e('informe a nota mínima') ?>" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.max" type="number" placeholder="<?php i::_e('informe a nota máxima') ?>" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.weight" type="number" placeholder="<?php i::_e('informe o peso da nota') ?>" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td>
                    <button ng-click="deleteCriterion(cri)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
            </tr>
        </table>
    </section>
    <button ng-click="addSection()" class="btn btn-default add"><?php i::_e('Adicionar seção de avaliação técnica') ?></button>


    <br><br><hr>

    <h4><?php i::_e('Políticas afirmativas') ?></h4>
    <p><?php i::_e('Configure abaixo as políticas afirmativas de avaliação técnica') ?>
    <section>
        <!-- {{data.fieldsAffirmativePolicies}} -->

        <header>
            <p style="font-size: 12px; margin: 0; white-space: nowrap;"> <?php i::_e('Porcentagem máxima das políticas afirmativas') ?> </p>
            <input ng-model="data.fieldsAffiermativePolicies.roof" type="number" step="0.01" value="0.00" min="0.00" max="100.00" placeholder="0,00" class="affirmative_policies-roof edit"> <span>%</span> <!-- ng-change="save({sections: data.sections})" ng-model-options='{ debounce: data.debounce }' -->
        </header>

        <table>
            <tr>
                <th class="policy-percent"><?php i::_e('Porcentagem') ?></th>
                <th class="policy-field"><?php i::_e('Campo') ?></th>
                <th class="policy-value"><?php i::_e('Valor') ?></th>
                <th>
                    <button ng-click="addPolicy()" class="btn btn-default add" title="<?php i::_e('Adicionar critério') ?>"></button>
                </th>
            </tr>

    
            <tr ng-repeat="(key,policy) in data.affirmativePolicies track by $index" id="{{policy.id}}" > <!-- ng-if="!data.affirmativePolicies" id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id" -->
                <td class="policy-percent"> 
                    <input ng-model="data.fieldsAffiermativePolicies.fieldPercent" type="number" step="0.01" value="{{policy.percentField}}" min="0.00" max="100.00" placeholder="<?php i::_e('informe a porcentagem do critério') ?>" class="affirmative_policies-roof edit"> <span>%</span>    
                    <!-- ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }' -->
                </td>                
                
                <td class="policy-field"> 
                    <select ng-model="data.fieldsAffiermativePolicies.field">
                        <option ng-repeat="field in data.registrationField" value="{{field.id}}"> {{field.title}} </option>
                    </select>
                </td>
                
                <td class="policy-value"> 
                    <input ng-model="data.fieldsAffiermativePolicies.value" type="text"> 
                </td>
                
                <td>
                    <button ng-click="removePolicy(policy)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
            </tr>

        </table>
    </section>
    <button ng-click="activeAffirmativePolicies()" class="btn btn-default add"><?php i::_e('Ativar seção de políticas afirmativas') ?></button>


    <br><br><hr>

    <h4><?php i::_e('Habilitar avaliação da exequibilidade da inscrição?'); ?></h4>
    <p>
        <?php i::_e('Ao habilitar esta configuração, os avaliadores deverão considerar a exequibilidade da inscrição.'); ?>
        <?php i::_e('Se a maioria dos avaliadores considerarem a inabilitação por exequibilidade, a mesma será marcada com o status de inválida para o dono do edital, que ainda assim poderá mudar seu status para válida.'); ?>
    </p>

    <label for="enableViability">
        <input type="radio" ng-model="data.enableViability" value="true" ng-change="save({enableViability: true})" name="enableViability" /> <?php i::_e('Habilitar'); ?>
    </label>

    <label for="enableViability">
        <input type="radio" ng-model="data.enableViability" value="false" ng-change="save({enableViability: false})" name="enableViability" /> <?php i::_e('Não habilitar'); ?>
    </label>
</div>

