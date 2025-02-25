<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    registration-workplan-form-goal
');
?>

<mc-card class="registration-details-workplan">
    <template #title>
        <h3 class="card__title">{{ workplansLabel }}</h3>
        <p><?= i::esc_attr__('Dados da ação cultural.') ?></p>
    </template>

    <template #content>
        <div v-if="workplan.projectDuration" class="field">
            <label><?= i::esc_attr__('Duração do projeto (meses)') ?></label>
            {{ workplan.projectDuration }}
        </div>

        <div v-if="workplan.culturalArtisticSegment" class="field">
            <label><?= i::esc_attr__('Segmento artistico-cultural') ?></label>
            {{ workplan.culturalArtisticSegment }}
        </div>

        <template v-if="registration.workplanProxy">
            <registration-workplan-form-goal v-for="(goal, goalIndex) in workplan.goals" :editable="editable" :goal="goal" :index="goalIndex" :key="goal.id" :registration="registration">
            </registration-workplan-form-goal>
        </template>
    </template>
</mc-card>
