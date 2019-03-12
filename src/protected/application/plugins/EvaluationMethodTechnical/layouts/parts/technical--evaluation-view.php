<?php
use MapasCulturais\i;
?>
<div ng-controller="TechnicalEvaluationMethodFormController" class="technical-evaluation-form">
    <div ng-if="!data.empty">
        <h3><?php i::_e('Avaliação Técnica'); ?>:</h3>
        <h4><?php i::_e('Pontuações'); ?>:</h4> 
        <section ng-repeat="section in ::data.sections">
             {{section.name}}: <strong>{{subtotalSection(section)}}</strong>
             <div ng-repeat="cri in ::data.criteria" ng-if="cri.sid == section.id">
                <span>
                    - {{cri.title}} <strong>{{evaluation[cri.id]}}</strong> (min: {{cri.min}} max: {{cri.max}} peso: {{cri.weight}})
                </span>
            </div>
        </section>
        <hr>
        <section class='total'>
            <?php i::_e('Total'); ?>: <strong>{{total(total)}}</strong><br>
            <?php i::_e('Avaliação máxima'); ?>: <strong>{{max(total)}}</strong>
        </section>
        <hr>
        <label>
            <strong><?php i::_e('Parecer Técnico') ?>:</strong>
            <p>{{evaluation['obs']}}</p>
        </label>
    </div>
</div>