<?php

use MapasCulturais\i;

?>
<?php $this->applyTemplateHook('print-static-graphics', 'before'); ?>

    <div class="charts-static">
        <?php $this->applyTemplateHook('print-static-graphics', 'begin'); ?>
        
        <?php if($module->registrationsByTime($opportunity)){?>
            <?php $this->part('registrationsByTime', ['data' => $module->registrationsByTime($opportunity), 'opportunity' => $opportunity, 'self' => $module, 'print' => true]);?>
        <?php } ?>

        <?php if($module->registrationsByStatus($opportunity)){?>
            <?php $this->part('registrationsDraftVsSent', ['data' => $module->registrationsByStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
            <div class="break-page"></div>
            <?php $this->part('registrationsStatus', ['data' => $module->registrationsByStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
        <?php } ?>

        <?php if ($opportunity->evaluationMethod->slug == 'technical') { ?>

            <?php if($module->registrationsByEvaluation($opportunity, $status)){?>
                <?php $this->part('registrationsEvaluation', ['data' => $module->registrationsByEvaluation($opportunity, $status), 'opportunity' => $opportunity, 'self' => $module, 'statusRegistration' => $status]);?>
            <?php } ?>

            <?php if($module->registrationsByEvaluationStatus($opportunity)){?>
                <?php $this->part('registrationsByEvaluationStatus', ['data' => $module->registrationsByEvaluationStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
            <?php } ?>

        <?php }else { ?>

            <?php if($module->registrationsByEvaluation($opportunity, $status)){?>
                <?php $this->part('registrationsEvaluation', ['data' => $module->registrationsByEvaluation($opportunity, $status), 'opportunity' => $opportunity, 'self' => $module , 'statusRegistration' => $status]);?>
            <?php } ?>

            <?php if($module->registrationsByEvaluationStatus($opportunity)){?>
                <?php $this->part('registrationsByEvaluationStatus', ['data' => $module->registrationsByEvaluationStatus($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
            <?php } ?>
        <?php } ?>            

        <?php if($module->registrationsByCategory($opportunity)){?>
            <?php $this->part('registrationsByCategory', ['data' => $module->registrationsByCategory($opportunity), 'opportunity' => $opportunity, 'self' => $module]);?>
        <?php } ?>

        <?php $this->applyTemplateHook('print-static-graphics', 'after'); ?>
    </div>
<?php $this->applyTemplateHook('print-static-graphics', 'end'); ?>