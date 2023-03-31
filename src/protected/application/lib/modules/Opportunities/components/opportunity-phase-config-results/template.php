<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    opportunity-phase-publish-date-config
');
?>

<mapas-card>
    <div class="config-phase grid-12">
        <opportunity-phase-publish-date-config :hide-checkbox="phase.publishTimestamp == null" :phase="phase" button-position="right" hideDescription />
    </div>
</mapas-card>