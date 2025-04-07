<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

$this->import('
    mc-stepper
');
?>
<mc-stepper :disabled-steps="disabledSteps" :steps="sections" :step="stepIndex" only-active-label @step-changed="goToSection($event)"></mc-stepper>