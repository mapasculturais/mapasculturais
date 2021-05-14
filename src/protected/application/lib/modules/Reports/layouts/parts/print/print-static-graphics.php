<?php

use MapasCulturais\i;

?>
<?php $this->applyTemplateHook('print-static-graphics', 'before'); ?>
<div ng-controller='Reports'>
    <?php $this->applyTemplateHook('print-static-graphics', 'begin'); ?>
    <div class="aba-content" id="reports">
        <div class="charts-static">
        
            <?php if($module->registrationsByTime($opportunity)){?>
                <?php $this->part('registrationsByTime', ['data' => $module->registrationsByTime($opportunity), 'opportunity' => $opportunity, 'self' => $module, 'print' => true]);?>
                <div class="break-page"></div>
            <?php } ?>

            <?php if($module->registrationsByStatus($opportunity)){?>
                <?php $this->part('registrationsDraftVsSent', ['data' => $module->registrationsByStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                <div class="break-page"></div>
                <?php $this->part('registrationsStatus', ['data' => $module->registrationsByStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                <div class="break-page"></div>
            <?php } ?>

            <?php if ($opportunity->evaluationMethod->slug == 'technical') { ?>

                <?php if($module->registrationsByEvaluation($opportunity)){?>
                    <?php $this->part('registrationsEvaluation', ['data' => $module->registrationsByEvaluation($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                    <div class="break-page"></div>
                <?php } ?>

                <?php if($module->registrationsByEvaluationStatus($opportunity)){?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $module->registrationsByEvaluationStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                    <div class="break-page"></div>
                <?php } ?>

            <?php }else { ?>

                <?php if($module->registrationsByEvaluation($opportunity)){?>
                    <?php $this->part('registrationsEvaluation', ['data' => $module->registrationsByEvaluation($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                    <div class="break-page"></div>
                <?php } ?>

                <?php if($module->registrationsByEvaluationStatus($opportunity)){?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $module->registrationsByEvaluationStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                    <div class="break-page"></div>
                <?php } ?>
            <?php } ?>            

            <?php if($module->registrationsByCategory($opportunity)){?>
                <?php $this->part('registrationsByCategory', ['data' => $module->registrationsByCategory($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
                <div class="break-page"></div>
            <?php } ?>
        </div>
    </div>
    <?php $this->applyTemplateHook('print-static-graphics', 'after'); ?>
</div>
<?php $this->applyTemplateHook('print-static-graphics', 'end'); ?>

