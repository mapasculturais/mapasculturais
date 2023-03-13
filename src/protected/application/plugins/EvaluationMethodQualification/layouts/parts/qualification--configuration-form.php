<?php

use MapasCulturais\i;
?>
<div ng-controller="QualificationEvaluationMethodConfigurationController" class="technical-evaluation-configuration registration-fieldset">
    <h4><?php i::_e('Critérios') ?></h4>
    <p><?php i::_e('Configure abaixo os critérios de avaliação de habilitação documental') ?>
    <section id="{{section.id}}" ng-repeat="section in data.sections">
        <header>
            <input ng-model="section.name" placeholder="<?php i::_e('informe o nome da seção') ?>" class="section-name edit" ng-change="save({sections: data.sections})" ng-model-options='{ debounce: data.debounce }'>
            <button ng-if="section.name.trim().length > 0" ng-click="deleteSection(section)" class="btn btn-danger delete alignright"><?php i::_e('Remover seção') ?></button>
            <button ng-if="section.name.trim().length == 0" ng-click="deleteSection(section)" class="btn btn-default delete alignright"><?php i::_e('Cancelar') ?></button>
        </header>

        <table>
            <tr>
                <th class="criterion-title"><?php i::_e('Título do critério') ?></th>
                <th>
                    <button ng-click="addCriterion(section)" class="btn btn-default add" title="<?php i::_e('Adicionar sessão') ?>"></button>
                </th>
            </tr>

            <tr id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id">
                <td class="criterion-title">
                    <select ng-model="data.qualifications[cri.id].field">
                        <option ng-repeat="field in data.registrationFieldConfigurations" value="{{field.id}}">#{{field.id}} - {{field.title}} </option>
                    </select>
                </td>
                <td>
                    <button ng-click="editableCriterion(cri)" class="btn btn-warning editable" title="<?php i::_e('Editar critério') ?>"></button>
                    <button ng-click="deleteCriterion(cri)" class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
            </tr>
        </table>
    </section>
    <button ng-click="addSection()" class="btn btn-default add"><?php i::_e('Adicionar seção') ?></button>

    <br><br>
    <hr>
</div>