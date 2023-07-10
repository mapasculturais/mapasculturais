<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab,
    opportunity-phase-evaluation
');
?>

<mc-tab label="<?= i::__('Avaliações') ?>" slug="evaluations" v-if="isEvaluator">
    <div class="opportunity-container">
        <opportunity-phase-evaluation></opportunity-phase-evaluation>
    </div>
</mc-tab>