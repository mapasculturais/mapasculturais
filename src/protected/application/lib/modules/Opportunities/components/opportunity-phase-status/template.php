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
        <div v-if="entity.summary?.registrations && !entity.isLastPhase" class="col-12 grid-12">
            <div class="opportunity-phase-status__box col-6">
                <div class="opportunity-phase-status__status col-6">
                    <h4 class="bold"><?php i::_e("Status das inscrições") ?></h4>
                    <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}}</strong><strong> <?= i::__('inscrições') ?></strong></p>
                    <p v-if="entity.summary?.sent"><?= i::__("Quantidade de inscrições <strong>enviadas</strong>:") ?> <strong>{{entity.summary.sent}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                    <p v-if="entity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                    <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições <strong>rascunho</strong>:") ?> <strong>{{entity.summary.Draft}}</strong> <strong><?= i::__('inscrições') ?></strong></p>
                </div>
                <div class=" col-6 opportunity-phase-status__endbox">
                  <h5 class="bold"><?= i::__("A lista de inscrições pode ser acessada utilizando o botão abaixo")?></h5>
                    <mc-link v-if="tab=='registrations' && entity.isFirstPhase" :entity="entity" class="button button--primary button--icon" icon="external" route="registrations" right-icon >
                        <h4 class="semibold"><?= i::__("Conferir lista de inscrições") ?></h4>
                    </mc-link>
                </div>
       
            </div>
        </div>
        <div :class="[{'col-12':  tab!='registrations' && entity.isFirstPhase}, 'opportunity-phase-status_action--center col-12 grid-12']">
            <opportunity-phase-publish-config-registration v-if="entity.isLastPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox :class="col-12"></opportunity-phase-publish-config-registration>
            
        </div>

        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div class="opportunity-phase-status__line col-12"></div>
            <opportunity-phase-publish-config-registration v-if="!entity.isLastPhase && !entity.isFirstPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
        </template>
    </div>
</mc-card>