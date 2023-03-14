<?php

use MapasCulturais\i;

$plugin = $app->plugins['EvaluationMethodTechnical'];

$params = ['registration' => $entity, 'opportunity' => $opportunity];
?>
<?php $this->applyTemplateHook('evaluationForm.technical', 'before', $params); ?>
<div ng-controller="TechnicalEvaluationMethodFormController" class="technical-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.technical', 'begin', $params); ?>
   <div>
       <strong> <?php i::_e('Categoria:criação,montagem e/ou circulação de Espetáculos/Pessoa Física') ?></strong>
        <br><br>
        <strong> <?php i::_e('Corrículo') ?> </strong>
        <select style="width:300px" name="data[obs]" ng-model="evaluation['obs']">
        <option>Habilitado</option>
        <option>qualquer coisa2</option>
     </select>
   </div>
   <div>
   <strong> <?php i::_e('Ficha técnica')?></strong>
        <select style="width:300px" name="data[obs]" ng-model="evaluation['obs']">
        <option>Não se aplica</option>
        <option>qualquer coisa2</option>
     </select>
   </div>
   <div>
        <strong> <?php i::_e('Comprovante de endereço') ?> </strong>
        <select style="width:300px" name="data[obs]" ng-model="evaluation['obs']">
        <option>Sem comprovante de endereço</option>
        <option>qualquer coisa2</option>
     </select>
   </div>
    
    <label>
        <strong><?php i::_e('Observações') ?> <strong>
        <textarea name="data[obs]" ng-model="evaluation['obs']"></textarea>
    </label>
    <br><br>
    <div>
    <label>
       <strong> <?php i::_e('Status:inabilitado')?> </strong>
    </label>
    </div>
   
    <?php $this->applyTemplateHook('evaluationForm.technical', 'end', $params); ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.technical', 'after', $params); ?>