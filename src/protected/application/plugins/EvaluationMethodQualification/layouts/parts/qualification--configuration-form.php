<?php

use MapasCulturais\i;
?>
<div ng-controller="QualificationEvaluationMethodConfigurationController" class="qualification-evaluation-configuration registration-fieldset">
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
                <th class="criterion-title"><?php i::_e('Descrição') ?></th>
                <th class="criterion-title"><?php i::_e('Opções') ?></th>
                <th>
                    <button ng-click="addCriterion(section)" class="btn btn-default add" title="<?php i::_e('Adicionar critétio') ?>"> <?php i::_e('Adicionar critétio') ?></button>
                </th>
            </tr>

            <tr id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id">
                <td>
                    <input type="text" ng-model="cri.description" ng-model-options='{ debounce: data.debounce }' placeholder="<?php i::_e('Descrição do critério') ?>" class="section-name edit" ng-change="save({sections: data.sections})">
                    <edit-box id="{{cri.id}}" position="right" title="<?php i::esc_attr_e("Informe as opções do critério separadas quebrando linha");?>" cancel-label="<?php i::esc_attr_e("Fechar");?>" close-on-cancel='true' spinner-condition="data.spinner">
                        <textarea ng-model="data.options[cri.id]" ng-model-options='{ debounce: data.debounce }' cols="70" rows="5" ng-change="save({sections: data.sections})"></textarea>
                    </edit-box>
                </td>
                <td>
                    <a id="delete-account--button" ng-click="editbox.open(cri.id, $event)" rel='noopener noreferrer' class="btn btn-primary add"><?php i::_e('Definir opções') ?></a>
                    <button ng-click="deleteCriterion(cri)" ng-model-options='{ debounce: data.debounce }' class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                </td>
                <td></td>
            </tr>
        </table>
    </section>
    <button ng-click="addSection()" class="btn btn-default add"><?php i::_e('Adicionar seção') ?></button>

    <br><br>
    <hr>
</div>