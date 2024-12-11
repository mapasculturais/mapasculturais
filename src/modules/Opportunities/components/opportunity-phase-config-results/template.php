<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-card
    opportunity-phase-publish-date-config
    opportunity-appeal-phase-config
');
?>
<mc-card>
    <div class="config-phase grid-12">
        <opportunity-phase-publish-date-config :phase="phase" :phases="phases" hide-description hide-button useSealsCertification></opportunity-phase-publish-date-config>
        <opportunity-appeal-phase-config :phase="phase" :phases="phases"></opportunity-appeal-phase-config>

        <div class="col-12 sm:col-12">
            <?php $this->applyComponentHook('bottom') ?>
        </div>
    </div>
</mc-card>