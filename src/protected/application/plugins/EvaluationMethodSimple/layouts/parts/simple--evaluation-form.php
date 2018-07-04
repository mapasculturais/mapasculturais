<?php
use MapasCulturais\i;
?>


<div ng-controller="SimpleEvaluationForm" >
    <div class="simple-evaluation-form">
        <h4><?php echo $evaluationMethod->getName(); ?></h4>
        <mc-select class="{{getStatusSlug(data.registration.status)}}" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus" placeholder="<?php i::_e('Selecione o status') ?>"></mc-select>
        <input type="hidden" name="data[status]" value="{{data.registration}}"/>
        <label class="textarea-label">
            <?php i::_e('Justificativa / Observações') ?><br>
            <textarea name="data[obs]">{{data.obs}}</textarea>
        </label>
    </div>
</div>
