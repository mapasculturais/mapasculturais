<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    opportunity-phase-evaluation
    panel--appeal-correction-assignments
');
?>

<mc-tab label="<?= i::__('Avaliações') ?>" slug="evaluations" v-if="showEvaluationsTab">
    <div class="opportunity-container">
        <panel--appeal-correction-assignments
            v-if="hasAppealCorrections"
            :opportunity-id="entity.id"
            hide-when-empty
            title="<?= i::esc_attr__('Correções designadas') ?>"
        ></panel--appeal-correction-assignments>
        <opportunity-phase-evaluation v-if="isEvaluator"></opportunity-phase-evaluation>
    </div>
</mc-tab>