<?php
use MapasCulturais\i;
?>
<div ng-controller="QualificationEvaluationMethodFormController" class="qualification-evaluation-form">
    <div ng-if="!data.empty">
        <strong><?php i::_e('Resultados das seções'); ?>:</strong>
        <section ng-repeat="section in ::data.sections">
             {{section.name}}: 
             <strong ng-if="subtotalSection(section) == '<?php i::_e('Habilitado')?>'" class="approved">{{subtotalSection(section)}}</strong>
             <strong ng-if="subtotalSection(section) == '<?php i::_e('Inabilitado')?>'" class="repproved">{{subtotalSection(section)}}</strong>
            
             <div ng-repeat="cri in ::data.criteria" ng-if="cri.sid == section.id">
                <small ng-if="evaluation[cri.id] == '<?php i::_e('Habilitado')?>'">
                    <strong>&nbsp&nbsp {{cri.name}}:</strong> 
                    <span class="approved">{{evaluation[cri.id]}}</span>
                </small>
                <small ng-if="evaluation[cri.id] == '<?php i::_e('Inabilitado')?>'">
                    <strong>&nbsp&nbsp {{cri.name}}:</strong>
                    <span class="approved">{{evaluation[cri.id]}}</span>
                </small>
             </div>
             <hr>
        </section>
        <section class='total'>
            <?php i::_e('Status'); ?>: 
            <strong ng-if="total() == '<?php i::_e('Habilitado')?>'" class="approved">{{total(total)}}</strong>
            <strong ng-if="total() == '<?php i::_e('Inabilitado')?>'" class="repproved">{{total(total)}}</strong><br>
        </section>
        <hr>
        <label>
            <strong><?php i::_e('Observações') ?>:</strong>
            <p>{{evaluation['obs']}}</p>
        </label>
    </div>
</div>