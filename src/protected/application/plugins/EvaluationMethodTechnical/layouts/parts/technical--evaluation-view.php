<?php
use MapasCulturais\i;
?>
<div ng-controller="TechnicalEvaluationMethodFormController" class="technical-evaluation-form">
    <div ng-if="!data.empty">
        <h3><?php i::_e('Avaliação Técnica'); ?>:</h3>
        <h4><?php i::_e('Pontuações'); ?>:</h4> 
        <section ng-repeat="section in ::data.sections">
            <p>
                <strong>{{section.name}}</strong> <br>
                <?php i::_e('Avaliação'); ?> <strong> {{subtotalSection(section)}} </strong>
            </p>
            <table>
                <tr ng-repeat="cri in ::data.criteria" ng-if="cri.sid == section.id" >
                 <td> {{cri.title}} <br> (min: {{cri.min}} max: {{cri.max}} peso: {{cri.weight}}) </td>
                 <td style="padding:15px"> {{evaluation[cri.id]}} </td>
                </tr>
            </table> 
           
        </section>
        <hr>
        <section class='total'>
            <strong><?php i::_e('Total'); ?>: {{total(total)}}</strong><br>
            <?php i::_e('Avaliação máxima'); ?>: <strong>{{max(total)}}</strong>
        </section>
        <hr>
        <label>
            <strong><?php i::_e('Parecer Técnico') ?>:</strong>
            <table>
                <tr><td style="text-align:left; padding: 15px;">{{evaluation['obs']}}</td></tr>
            </table
        </label>
    </div>
</div>