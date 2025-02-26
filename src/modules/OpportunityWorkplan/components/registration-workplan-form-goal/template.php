<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    registration-workplan-form-delivery
');
?>

<div class="registration-details-workplan__goals">
    <div class="registration-details-workplan__header-goals">
        <h4 class="registration-details-workplan__goals-title">{{ goalsLabel }} - {{ goal.title }}</h4>
        
        <div class="registration-details-workplan__goals-status">
            <mc-select v-if="editable" aria-label="<?= i::esc_attr__('Status') ?>" @change-option="proxy.status = $event.value">
                <option v-for="(label, value) of statusOptions" :key="value" :value="value">{{ label }}</option>
            </mc-select>

            <button class="button" type="button" :aria-label="expanded ? '<?php i::esc_attr__('Ocultar') ?>' : '<?php i::esc_attr__('Exibir') ?>'" @click="expanded = !expanded">
                <mc-icon :name="expanded ? 'up' : 'down'"></mc-icon>
            </button>
        </div>
    </div>

    <template v-if="expanded">
        <div v-if="goal.monthInitial" class="field">
            <label><?= i::__('Mês inicial') ?></label>
            {{ goal.monthInitial }}
        </div>

        <div v-if="goal.monthEnd" class="field">
            <label for="mes-final"><?= i::__('Mês final') ?></label>
            {{ goal.monthEnd }}
        </div>

        <div v-if="goal.title" class="field">
            <label><?= i::_e('Título') ?></label>
            {{ goal.title }}
        </div>

        <div v-if="goal.description" class="field">
            <label><?= i::__('Descrição') ?></label>
            {{ goal.description }}
        </div>

        <!-- Etapa do fazer cultural -->
        <div v-if="goal.culturalMakingStage" class="field">
            <label><?= i::__('Etapa do fazer cultural') ?></label>
            {{ goal.culturalMakingStage }}
        </div>

        <div class="field" v-if="editable || proxy.executionDetail">
            <label :for="`${vid}__executionDetail`"><?= i::__('Detalhamento da execução da meta') ?></label>
            <textarea v-if="editable" :id="`${vid}__executionDetail`" v-model="proxy.executionDetail" placeholder="<?= i::esc_attr__('Digite') ?>"></textarea>
            <span v-else>{{ proxy.executionDetail }}</span>
        </div>

        <registration-workplan-form-delivery v-for="delivery in goal.deliveries" :delivery="delivery" :editable="editable" :key="delivery.id" :registration="registration">
        </registration-workplan-form-delivery>
    </template>
</div>
