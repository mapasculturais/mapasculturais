<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

$this->import('
    stepper
');
?>
<stepper :steps="sections" only-active-label small @step-changed="goToSection($event)"></stepper>