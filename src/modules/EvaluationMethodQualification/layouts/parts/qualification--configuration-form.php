<?php

use MapasCulturais\i;
?>
<div ng-controller="QualificationEvaluationMethodConfigurationController" class="qualification-evaluation-configuration registration-fieldset">
    <h4><?php i::_e('Critérios') ?></h4>
    <p><?php i::_e('Configure abaixo os critérios de avaliação de habilitação documental') ?>
    <section id="{{section.id}}" ng-repeat="section in data.sections">
        <hr>
        <header>
            <input ng-model="section.name" placeholder="<?php i::_e('informe o nome da seção') ?>" class="section-name edit" ng-change="save({sections: data.sections})" ng-model-options='{ debounce: data.debounce }'>
            <button class="btn btn-default add" ng-click="addCriterion(section)" title="<?php i::_e('Adicionar critério') ?>"> <?php i::_e('Adicionar critério') ?></button>
            <button ng-if="section.name.trim().length > 0" ng-click="deleteSection(section)" class="btn btn-danger delete alignright"><?php i::_e('Remover seção') ?></button>
            <button ng-if="section.name.trim().length == 0" ng-click="deleteSection(section)" class="btn btn-default delete alignright"><?php i::_e('Cancelar') ?></button>
        </header>

        <table>
            <tr>
                <th class="criterion-title"><?php i::_e('Nome do critério') ?></th>
                <th class="criterion-title"><?php i::_e('Opções') ?></th>
            </tr>

            <tr id="{{cri.id}}" ng-repeat="cri in data.criteria" ng-if="cri.sid == section.id">
                <td>
                    <div class="criteria-name">
                        <input type="text" ng-model="cri.name" ng-model-options='{ debounce: data.debounce }' placeholder="<?php i::_e('Nome do critério') ?>" class="section-name edit" ng-change="save({sections: data.sections})">
                    </div>
                </td>
                <td>
                    <div class="criteria-options">
                        <a id="delete-account--button" ng-click="editbox.open(cri.id, $event)" rel='noopener noreferrer' class="btn btn-primary add"><?php i::_e('Configurar critério') ?></a>
                        <edit-box id="{{cri.id}}" position="right" title="<?php i::esc_attr_e("Configuração do critério"); ?> {{cri.name}}" cancel-label="<?php i::esc_attr_e("Fechar"); ?>" close-on-cancel='true' spinner-condition="data.spinner">

                            <div>
                                <label> <?php i::esc_attr_e("Descrição do critério"); ?></label>
                                <textarea ng-model="cri.description" ng-model-options='{ debounce: data.debounce }' cols="75" rows="3" ng-change="save({sections: data.sections})"></textarea>
                            </div>
                            <div>
                                <label> <?php i::esc_attr_e("Opções ou motivos de "); ?> <strong><?php i::esc_attr_e("inabilitação"); ?></strong></label>
                                <textarea ng-model="data.options[cri.id]" ng-model-options='{ debounce: data.debounce }' cols="70" rows="5" ng-change="save({sections: data.sections})" placeholder="<?php i::_e('As opções Habilitado e inabilitado já são definidas automaticamente pelo sistema') ?>"></textarea>
                            </div>
                            <div>
                                <label>
                                    <input type="checkbox" ng-model="cri.notApplyOption" ng-model-options='{ debounce: data.debounce }' ng-change="save({sections: data.sections})">
                                    <span> <?php i::_e('Habilitar a opção Não se aplica') ?></span>
                                </label>
                            </div>
                            <br>
                            <hr>
                            <div class="comments">
                                <div>
                                    <strong><?php i::_e("Observações"); ?></strong><br>
                                    <ul>
                                        <li><?php i::_e('As opções devem estar configuradas cada uma em uma linha') ?></li> <br>
                                    </ul>

                                </div>
                            </div>
                        </edit-box>
                        <button ng-click="deleteCriterion(cri)" ng-model-options='{ debounce: data.debounce }' class="btn btn-danger delete" title="<?php i::_e('Remover critério') ?>"></button>
                    </div>
                </td>
            </tr>
        </table>
    </section>
    <button ng-click="addSection()" class="btn btn-default add"><?php i::_e('Adicionar seção') ?></button>

    <br><br>
    <hr>
</div>