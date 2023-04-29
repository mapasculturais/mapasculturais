<?php
use MapasCulturais\i;

if (!$entity->lastPhase->publishedRegistrations) {
    return;
}
?>

<tab label="<?= i::__('Resultados') ?>" slug="results" v-if="entity.currentUserPermissions.view">
    <div class="opportunity-container">
        <v1-embed-tool route="registrationmanager" :id="entity.id"></v1-embed-tool>
    </div>
</tab>
