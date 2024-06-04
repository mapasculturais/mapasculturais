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
    opportunity-results-table
');
?>

<mc-tab label="<?= i::__('Resultados') ?>" slug="results">
    <div class="opportunity-container">
        <opportunity-results-table></opportunity-results-table>
    </div>
</mc-tab>
