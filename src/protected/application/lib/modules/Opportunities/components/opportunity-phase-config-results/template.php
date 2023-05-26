<?php
use MapasCulturais\i;
$this->import('
    opportunity-phase-publish-date-config
');
?>

<mc-card>
    <div class="config-phase grid-12">
        <opportunity-phase-publish-date-config :phase="phase" :phases="phases" hide-description hide-button></opportunity-phase-publish-date-config>
    </div>
</mc-card>