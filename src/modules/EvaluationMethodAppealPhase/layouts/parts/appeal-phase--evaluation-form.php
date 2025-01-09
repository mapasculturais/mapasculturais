<?php
use MapasCulturais\i;
$params = ['registration' => $entity, 'opportunity' => $opportunity]; 
?>

<?php $this->applyTemplateHook('evaluationForm.appealPhase', 'before', $params); ?>
<div ng-controller="AppealPhaseEvaluationForm" >
    <?php $this->applyTemplateHook('evaluationForm.appealPhase', 'begin', $params); ?>
    <div class="appeal-phase-evaluation-form">
        <h4><?php echo $evaluationMethod->getName(); ?></h4>
        <mc-select class="{{getStatusSlug(data.registration.status)}} autosave" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus" placeholder="<?php i::_e('Selecione o status') ?>"></mc-select>
        <input type="hidden" name="data[status]" value="{{data.registration}}"/>
        <label class="textarea-label">
            <?php i::_e('Justificativa / Observações') ?><br>
            <textarea ng-model="data.obs" name="data[obs]">{{data.obs}}</textarea>
        </label>
    </div>
    <?php $this->applyTemplateHook('evaluationForm.appealPhase', 'end', $params); ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.appealPhase', 'after', $params); ?>
