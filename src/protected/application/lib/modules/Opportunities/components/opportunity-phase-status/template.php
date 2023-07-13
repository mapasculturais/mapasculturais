<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-phase-publish-config-registration
')
?>
<mc-card>
    <div class="grid-12 opportunity-phase-status">
        <div v-if="entity.summary?.registrations" class="col-12">
            <h3><?php i::_e("Status das inscrições") ?></h3>
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
        </div>
        <div class="col-12 opportunity-phase-status_action--center">
             <opportunity-phase-publish-config-registration v-if="entity.isLastPhase" :phase="entity" :phases="phases" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
      
            <mc-link :entity="entity" class="opportunity-phase-status_action--button" route="registrations" right-icon>
              <?= i::__("Acessar lista de pessoas inscritas") ?>
            </mc-link>
        </div>

        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div class="config-phase__line col-12"></div>
            <opportunity-phase-publish-config-registration v-if="!entity.isLastPhase" :phase="entity" :phases="phases" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
        </template>
    </div>
</mc-card>