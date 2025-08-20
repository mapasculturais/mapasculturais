<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-card
    opportunity-phase-config-status
    opportunity-phase-publish-date-config
    opportunity-appeal-phase-config
    seals-certifier
');
?>
<mc-card>
    <div class="config-phase grid-12">
        <opportunity-phase-config-status :phase="phase"></opportunity-phase-config-status>

        <opportunity-phase-publish-date-config :phase="phase" :phases="phases" hide-description hide-button useSealsCertification></opportunity-phase-publish-date-config>
        <opportunity-appeal-phase-config :phase="phase" :phases="phases" :tab="tab"></opportunity-appeal-phase-config>

        <seals-certifier :entity="firstPhase" :editable="seals.length > 0"></seals-certifier>

        <div class="col-12 sm:col-12">
            <?php $this->applyComponentHook('bottom') ?>
        </div>
    </div>
</mc-card>