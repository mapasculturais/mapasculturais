<?php
use MapasCulturais\i;
$this->import('
    opportunity-phase-publish-date-config
');
?>

<mapas-card>
    <div class="config-phase grid-12">
        <opportunity-phase-publish-date-config :phase="phase" :phases="phases" :hide-checkbox="!!phase.publishTimestamp" hide-description />
    </div>
</mapas-card>