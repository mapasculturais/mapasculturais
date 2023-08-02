<?php

use MapasCulturais\i;

$module = $app->modules['EvaluationMethodQualification'];

$params = ['registration' => $entity, 'opportunity' => $opportunity];
?>
<?php $this->applyTemplateHook('evaluationForm.qualification', 'before', $params); ?>
<div ng-controller="QualificationEvaluationMethodFormController" class="qualification-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.qualification', 'begin', $params); ?>
    <section ng-repeat="section in ::data.sections">
        <table>
            <tr>
                <th colspan="2">
                    {{section.name}}
                </th>
            </tr>

            <tr ng-repeat="cri in ::data.criteria" ng-if="cri.sid == section.id">
                <td>
                    <div>
                        <?php echo $module->step ?><label for="{{cri.id}}">
                        <div class="tooltip">
                            <i class="fa fa-info-circle"></i>
                            <div class="tooltiptext" ng-if="cri.description">{{cri.description}}</div>
                            <div class="tooltiptext" ng-if="!cri.description">{{cri.name}}</div>
                        </div>
                            {{cri.name}}:
                        </label>
                    </div>
                </td>
                <td>
                    <select class="autosave" name="data[{{cri.id}}]" ng-model="evaluation[cri.id]">
                        <option value=""><?php i::_e('Selecione') ?></option>
                        <option ng-repeat="option in cri.options track by $index">{{option}}</option>
                    </select>
                </td>
            </tr>

            <tr class="subtotal">
                <td><?php i::_e('Resultado da seção') ?></td>
                <td ng-if="subtotalSection(section) == '<?php i::_e('Não avaliada')?>'" class="repproved">{{subtotalSection(section)}}</td>
                <td ng-if="subtotalSection(section) == '<?php i::_e('Habilitado')?>'" class="approved">{{subtotalSection(section)}}</td>
                <td ng-if="subtotalSection(section) == '<?php i::_e('Inabilitado')?>'" class="repproved">{{subtotalSection(section)}}</td>
            </tr>
        </table>
    </section>
    <hr>
    <label>
        <?php i::_e('Observações') ?>
        <textarea class="autosave" name="data[obs]" ng-model="evaluation['obs']"></textarea>
    </label>
    <hr>

    <div class='total'>
        <?php i::_e('Status'); ?>:
        
        <strong ng-if="total() == '<?php i::_e('Não avaliada')?>'" class="repproved">{{total()}}</strong>
        <strong ng-if="total() == '<?php i::_e('Habilitado')?>'" class="approved">{{total()}}</strong>
        <strong ng-if="total() == '<?php i::_e('Inabilitado')?>'" class="repproved">{{total()}}</strong><br>
    </div>
    <?php $this->applyTemplateHook('evaluationForm.qualification', 'end', $params); ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.qualification', 'after', $params); ?>