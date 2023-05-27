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
<mc-stepper :steps="sections" only-active-label small @step-changed="goToSection($event)"></mc-stepper>