<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-confirm-button
    mc-icon
');
?>

<div class="affirmative-policies--quota-configuration">
    <h4 class="bold"><?= i::__('Configuração das Cotas por Categoria') ?></h4>

    <div v-if="entity.quotaConfiguration && entity.quotaConfiguration.rules.length > 0">
        <input type="number" v-model="totalPercentage" @change="updateTotalQuotas" @blur="autoSave" min="0" max="100"> %
        <input type="number" v-model="totalQuota" @change="updateQuotaPercentage" @blur="autoSave" min="0" :max="totalVacancies">

        <div v-for="(quota, index) in entity.quotaConfiguration.rules" :key="index">
            <h5 class="bold field__title"><?= i::__('Cota') ?> {{index+1}}</h5>

            <select v-model="quota.fieldName">
                <option v-for="(item, index) in fields" :value="item.fieldName">{{ '#' + item.id + ' ' + item.title }}</option>
            </select>

            <input type="number" v-model="quota.rulesPercentages[index]" @change="updateRuleQuotas(quota, index)" min="0" max="100"> %
            <input type="number" v-model="quota.vacancies" @change="updateRuleQuotaPercentage(quota, index)" min="0" :max="totalQuota">

            <div v-if="getFieldType(quota) === 'select' || getFieldType(quota) === 'multiselect'">
                <label v-for="option in getFieldOptions(quota)">
                    <input type="checkbox" :value="option" v-model="quota.eligibleValues" @change="autoSave()">
                    {{option}}
                </label>
            </div>

            <div v-if="getFieldType(quota) === 'checkbox' || getFieldType(quota) === 'boolean'">
                <label>
                    <input type="radio" :name="quota.fieldName + ':' + index" :value="true" v-model="quota.eligibleValues" @change="autoSave()">
                    <?php i::__('Sim / Marcado') ?>
                </label>
                <label>
                    <input type="radio" :name="quota.fieldName + ':' + index" :value="false" v-model="quota.eligibleValues" @change="autoSave()">
                    <?php i::__('Não / Desmarcado') ?>
                </label>
            </div>

            <mc-confirm-button @confirm="removeConfig(index)">
                <template #button="{open}">
                    <div class="field__trash">
                        <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                    </div>
                </template>
                <template #message="message">
                    <?= i::__('Deseja deletar a cota?') ?>
                </template>
            </mc-confirm-button>
        </div>
    </div>

    <button @click="addConfig();" class="button button--primary button--icon">
        <mc-icon name="add"></mc-icon>
        <label v-if="entity.quotaConfiguration && entity.quotaConfiguration.rules.length > 0">
            <?php i::_e("Adicionar categoria") ?>
        </label>
        <label v-else>
            <?php i::_e("Configurar Cotas por Categoria") ?>
        </label>
    </button>
</div>