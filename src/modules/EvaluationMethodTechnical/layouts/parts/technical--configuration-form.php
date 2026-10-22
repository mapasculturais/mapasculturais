<?php
use MapasCulturais\i;
?>
<div ng-controller="TechnicalEvaluationMethodConfigurationController" class="technical-evaluation-configuration registration-fieldset">
    <div class="technical-evaluation-configuration--score-mode">
        <label>
            <?php i::_e('Modo de atribuição de notas') ?>
            <select ng-model="data.scoreMode" ng-change="save()" ng-disabled="data.hasStartedEvaluations">
                <option value="typed"><?php i::_e('Nota digitada') ?></option>
                <option value="fixed"><?php i::_e('Notas fixas') ?></option>
            </select>
        </label>
        <p ng-if="data.hasStartedEvaluations"><?php i::_e('O modo e as notas permitidas não podem ser alterados após o início das avaliações.') ?></p>
        <div ng-if="data.scoreMode == 'fixed'" ng-repeat="cri in data.criteria">
            <label>
                {{cri.title}} — <?php i::_e('Notas permitidas') ?>
                <input type="text" ng-model="data.scoreOptionInputs[cri.id]" ng-change="updateScoreOptions(cri)" ng-blur="checkScoreOptions(cri)" ng-model-options='{ debounce: data.debounce }' ng-disabled="data.hasStartedEvaluations" placeholder="0,5,10">
            </label>
            <small><?php i::_e('Separe por vírgula. Para decimais, use ponto: 0,2.5,5.') ?></small>
        </div>
    </div>
    <!-- <div class="technical-evaluation-configuration--criteria">
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
    </div> -->

    <?php $this->part('technical--affirmative-polices-configuration')?>

    <br><br><hr>

    <!-- <div class="technical-evaluation-configuration--feasibility">
        <h4><?php // i::_e('Habilitar avaliação da exequibilidade da inscrição?'); ?></h4>
        <p>
            <?php //i::_e('Ao habilitar esta configuração, os avaliadores deverão considerar a exequibilidade da inscrição.'); ?>
            <?php //i::_e('Se a maioria dos avaliadores considerarem a inabilitação por exequibilidade, a mesma será marcada com o status de inválida para o dono do edital, que ainda assim poderá mudar seu status para válida.'); ?>
        </p>

        <div class="options">
            <label for="enableViability">
                <input type="radio" ng-model="data.enableViability" value="true" ng-change="save({enableViability: true})" name="enableViability" /> <?php i::_e('Habilitar'); ?>
            </label>
    
            <label for="enableViability">
                <input type="radio" ng-model="data.enableViability" value="false" ng-change="save({enableViability: false})" name="enableViability" /> <?php i::_e('Não habilitar'); ?>
            </label>
        </div>
    </div> -->
</div>

