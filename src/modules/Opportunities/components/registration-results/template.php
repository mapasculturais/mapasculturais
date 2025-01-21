<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;



$entity = $this->controller->requestedEntity;
?>
{{console.log(registration.consolidatedDetails)}}
<div v-if="registration.consolidatedDetails?.sentEvaluationCount" class="registration-results">  
    <mc-modal :title="modalTitle" classes="registration-results__modal">
        <template #default>
            <?php foreach($app->getRegisteredEvaluationMethods(true) as $evaluation_method): ?>
                <div v-if="phase.type?.id == '<?= $evaluation_method->slug ?>'" class="registration-results__content">
                    <?php $this->part($evaluation_method->slug . '/evaluations-details') ?>
                </div>
            <?php endforeach ?>

            <!-- <div v-if="registration.opportunity.status === -20 && !hideAppealStatus" class="opportunity-phases-timeline__box">
                <div class="opportunity-phases-timeline__content">
                    <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado do recurso:')?></label>
                    <div class="opportunity-phases-timeline__status">
                        <mc-icon name="circle" :class="verifyState(appealRegistration)"></mc-icon>
                        <p v-if="appealRegistration.status == 10"><?= i::__('Deferido') ?></p>
                        <p v-if="appealRegistration.status == 3"><?= i::__('Indeferido') ?></p>
                        <p v-if="appealRegistration.status == 2"><?= i::__('Recurso inválido') ?></p>
                        <p v-if="appealRegistration.status == 1"><?= i::__('Aguardando resposta') ?></p>
                        <p v-if="appealRegistration.status == 0"><?= i::__('Recurso não enviado') ?></p>
                    </div>
                </div>
            </div> -->
                {{console.log(appealRegistration)}}
            <div v-if="!appealRegistration && !hideAppealStatus" class="registration-results__request-appeal">
                <button class="button button--primary" @click="createAppealPhaseRegistration()"><?= i::__('Solicitar recurso') ?></button>
            </div>
        </template>

        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir detalhamento') ?></button>
        </template>
    </mc-modal>
</div>
