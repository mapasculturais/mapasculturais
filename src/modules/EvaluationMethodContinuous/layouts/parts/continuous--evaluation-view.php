<?php use MapasCulturais\i; ?>
<div ng-controller="ContinuousEvaluationForm" class="continuous-evaluation-view">
    <div ng-if="data.registration">
        <?php i::_e('Avaliação'); ?>: <strong>{{getStatusLabel(data.registration)}}</strong>
        <p style="white-space: pre-line;">
            {{data.obs}}
        </p>
    </div>
</div>
