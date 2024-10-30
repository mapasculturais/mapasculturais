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
<mc-stepper :steps="sections" :stepped="stepIndex" only-active-label disable-navigation small @step-changed="goToSection($event)"></mc-stepper>