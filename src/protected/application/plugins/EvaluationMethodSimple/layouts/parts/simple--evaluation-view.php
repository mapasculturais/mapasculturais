<?php use MapasCulturais\i; ?>
<div ng-controller="SimpleEvaluationForm" class="simple-evaluation-view">
    <div ng-if="data.registration">
        <div class="simple-evaluation-form">
            <h4><?php echo $evaluationMethod->getName(); ?></h4>
            <?php i::_e('Avaliação'); ?>: <strong>{{getStatusLabel(data.registration)}}</strong>
            <br>
            <label class="textarea-label"><?php i::_e('Justificativa / Observações') ?> </label>
            <br>
            <p>{{data.obs}}</p>
            
        </div>
       
    </div>
    
</div>
