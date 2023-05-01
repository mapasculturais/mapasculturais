<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-notification
    opportunity-phase-publish-date-config
');
?>

<mapas-card>
    <div class="grid-12 opportunity-phase-list-evaluation">
        <div class="col-6">
            <h3><?php i::_e("Status das inscrições") ?></h3>
            <!-- <p><?= i::__("Status da avaliação:") ?> <strong>Em andamento</strong></p> -->
            <p v-if="entity.summary.registrations"><?= i::__("Quantidade inscrições:") ?> <strong>{{entity.summary.registrations}}</strong> <?php i::_e('inscrições') ?></p>
            <p v-if="entity.summary.evaluated"><?= i::__("Quantidade de inscrições <strong>avaliadas</strong>:") ?> <strong>{{entity.summary.evaluated}}</strong> <?php i::_e('inscrições') ?></p>
            <p v-if="entity.summary.Approved"><?= i::__("Quantidade de inscrições <strong>selecionadas</strong>:") ?> <strong>{{entity.summary.Approved}}</strong> <?php i::_e('inscrições') ?></p>
            <p v-if="entity.summary.Waitlist"><?= i::__("Quantidade de inscrições <strong>suplentes</strong>:") ?> <strong>{{entity.summary.Waitlist}}</strong> <?php i::_e('inscrições') ?></p>
            <p v-if="entity.summary.Invalid"><?= i::__("Quantidade de inscrições <strong>inválidas</strong>:") ?> <strong>{{entity.summary.Invalid}}</strong> <?php i::_e('inscrições') ?></p>
            <p v-if="entity.summary.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.summary.Pending}}</strong> <?php i::_e('inscrições') ?></p>
        </div>
        <div class="col-6">
            <h3><?php i::_e("Status das avaliações") ?></h3>
            <p v-for="(value, label) in entity.summary.evaluations"><?= i::__("Quantidade de inscrições") ?> <strong>{{label.toLowerCase()}}</strong>: <strong>{{value}}</strong> <?php i::_e('inscrições') ?></p>
        </div>
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
            <mc-link :entity="entity.opportunity" class="opportunity-phase-list-data-collection_action--button" icon="external" route="registrations" right-icon>
              <?= i::__("Lista de inscrições da fase") ?>
            </mc-link>
        </div>
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
            <mc-link route="opportunity/opportunityEvaluations" :params="[entity.opportunity.id]" class="opportunity-phase-list-data-collection_action--button" icon="external" right-icon>
              <?= i::__("Lista de avaliações") ?>
            </mc-link>
        </div>
        <div class="config-phase__line-bottom col-12"></div>
        <opportunity-phase-publish-date-config :phase="entity.opportunity" :phases="phases" hide-datepicker></opportunity-phase-publish-date-config>
    </div>
</mapas-card>