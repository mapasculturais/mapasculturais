<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;



$entity = $this->controller->requestedEntity;
?>
<div v-if="showEvaluationDetails" class="registration-results">  
    <mc-modal :title="modalTitle" classes="registration-results__modal registration-results__modal--with-chat">
        <template #default>
            <?php foreach($app->getRegisteredEvaluationMethods(true) as $evaluation_method): ?>
                <div v-if="phase.type?.id == '<?= $evaluation_method->slug ?>'" class="registration-results__content">
                    <?php $this->part($evaluation_method->slug . '/evaluations-details') ?>
                </div>
            <?php endforeach ?>

            <div v-if="!hideAppealStatus && appealPhase && !appealRegistration" class="registration-results__request-appeal">
                <button class="button button--primary" @click="createAppealPhaseRegistration()"><?= i::__('Solicitar recurso') ?></button>
            </div>
        </template>

        <template #button="modal">
            <button  class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir detalhamento') ?></button>
        </template>
    </mc-modal>
</div>
