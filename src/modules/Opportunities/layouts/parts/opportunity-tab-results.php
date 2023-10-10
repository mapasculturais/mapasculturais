<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

if (!$entity->lastPhase->publishedRegistrations) {
    return;
}

$this->import('
    mc-tab
');
?>

<mc-tab label="<?= i::__('Resultados') ?>" slug="results">
    <div class="opportunity-container">
        <v1-embed-tool route="opportunityresults" :id="<?= $entity->lastPhase->id ?>" min-height="600px"></v1-embed-tool>
    </div>
</mc-tab>
