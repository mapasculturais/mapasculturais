<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;
?>
<div v-if="registration.consolidatedDetails.sentEvaluationCount" class="registration-results">  
    <mc-modal :title="`${phase.name} - ${registration.number}`" classes="registration-results__modal">
        <template #default>
            <?php foreach($app->getRegisteredEvaluationMethods() as $evaluation_method): ?>
                <div v-if="phase.type?.id == '<?= $evaluation_method->slug ?>'" class="registration-results__content">
                    <?php $this->part($evaluation_method->slug . '/evaluations-details') ?>
                </div>
            <?php endforeach ?>
        </template>

        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir detalhamento') ?></button>
        </template>
    </mc-modal>
</div>