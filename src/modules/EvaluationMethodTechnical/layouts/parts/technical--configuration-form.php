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
                <td class="criterion-title"><input ng-model="cri.title" placeholder="<?php i::_e('informe o título do critério') ?>" class="criteria-fields" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.min" type="number" placeholder="<?php i::_e('informe a nota mínima') ?>" class="criteria-fields" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.max" type="number" placeholder="<?php i::_e('informe a nota máxima') ?>" class="criteria-fields" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td class="criterion-num"><input ng-model="cri.weight" type="number" placeholder="<?php i::_e('informe o peso da nota') ?>" class="criteria-fields" ng-change="save({criteria: data.criteria})" ng-model-options='{ debounce: data.debounce }'></td>
                <td>
                    <button ng-click="deleteCriterion(cri)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
            </tr>
        </table>
    </section>
    <button ng-click="addSection()" class="btn btn-default add"><?php i::_e('Adicionar seção de avaliação técnica') ?></button>

    <?php $this->part('technical--affirmative-polices-configuration')?>

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

