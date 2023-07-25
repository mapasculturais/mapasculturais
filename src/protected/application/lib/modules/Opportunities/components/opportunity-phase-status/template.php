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
    <div :class="[{'grid-12': tab!='registrations'}, 'opportunity-phase-status']">
        <div v-if="entity.summary?.registrations && !entity.isLastPhase" class="col-12">
            <h3><?php i::_e("Status das inscrições") ?></h3>
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
        </div>
        <div v-if="tab=='registrations' && entity.isFirstPhase" class="opportunity-phase-status__line col-12"></div>
        <div :class="[{'col-12':  tab!='registrations' && entity.isFirstPhase}, 'opportunity-phase-status_action--center col-12 grid-12']">
            <opportunity-phase-publish-config-registration v-if="entity.isLastPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox :class="col-12"></opportunity-phase-publish-config-registration>
            <mc-link v-if="tab=='registrations' && entity.isFirstPhase" :entity="entity" class="opportunity-phase-status_action--button col-12" icon="external" route="registrations" right-icon >
                <h3><?= i::__("Acessar lista de pessoas inscritas") ?></h3>
            </mc-link>
        </div>

        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div class="config-phase__line col-12"></div>
            <opportunity-phase-publish-config-registration v-if="!entity.isLastPhase && !entity.isFirstPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
        </template>
    </div>
</mc-card>