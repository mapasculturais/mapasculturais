<?php
use MapasCulturais\i;

if (!$entity->lastPhase->publishedRegistrations) {
    return;
}
?>

<tab label="<?= i::__('Resultados') ?>" slug="results">
    <div class="opportunity-container">
        <v1-embed-tool route="opportunityresults" :id="<?= $entity->lastPhase->id ?>"></v1-embed-tool>
    </div>
</tab>
