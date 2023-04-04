<?php
use MapasCulturais\i;

$this->import('
    confirm-button
    opportunity-phase-publish-date-config
')
?>

<mapas-card>
    <div class="grid-12 opportunity-phase-list-data-collection">
        <div v-if="entity.summary?.registrations" class="col-12">
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade de inscrições:") ?> <strong>{{entity.summary.registrations}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Sent"><?= i::__("Quantidade de inscrições enviadas:") ?> <strong>{{entity.summary.Sent}} <?= i::__('inscrições') ?></strong></p>
            <p v-if="entity.summary?.Draft"><?= i::__("Quantidade de inscrições rascunho:") ?> <strong>{{entity.summary.Draft}} <?= i::__('inscrições') ?></strong></p>
        </div>
        <div class="col-4 sm:col-12 subscribe_prev_phase">
            <!-- TO DO -->
        </div>
        <div class="col-8 sm:col-12 subscribe_prev_phase">
            <p><strong><?= i::__("Ao trazer as inscrições, você garante que apenas participantes classificados na fase anterior sigam para a póxima fase.") ?></strong></p>
        </div>
        <div class="col-12 opportunity-phase-list-data-collection_action--center">
            <mc-link :entity="entity" class="opportunity-phase-list-data-collection_action--button" icon="external" route="registrations" right-icon>
              <?= i::__("Lista de inscrições da fase") ?>
            </mc-link>
        </div>
        <div class="config-phase__line-bottom col-12"></div>
        <opportunity-phase-publish-date-config :phase="entity" :hide-checkbox="!!entity.publishTimestamp" hide-datepicker></opportunity-phase-publish-date-config>
    </div>
</mapas-card>