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
<mc-stepper :steps="sections" :step="stepIndex" only-active-label :disable-navigation="disableNavigation" @step-changed="goToSection($event)"></mc-stepper>