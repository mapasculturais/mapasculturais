<?php
use MapasCulturais\i;
?>
<style>
    .evaluation-form--simple mc-select div.dropdown { width:100%; }
</style>

<div ng-controller="SimpleEvaluationForm">
    <h4><?php echo $evaluationMethod->getName(); ?></h4>
    <mc-select class="{{getStatusSlug(data.registration.status)}}" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus" placeholder="<?php i::_e('Selecione o status') ?>"></mc-select>
    <input type="hidden" name="data[status]" value="{{data.registration}}"/>
</div>
