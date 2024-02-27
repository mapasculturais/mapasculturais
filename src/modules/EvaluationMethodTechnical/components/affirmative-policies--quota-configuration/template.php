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

            <select v-model="quota.fieldName" @change="selectField(quota.fieldName, index)">
                <option v-for="(item, index) in entity.opportunity.affirmativePoliciesEligibleFields" :value="item.fieldName">{{ '#' + item.id + ' ' + item.title }}</option>
            </select>

            <input type="number" v-model="rulesPercentages[index]" @change="updateRuleQuotas(quota, index)" min="0" max="100"> %
            <input type="number" v-model="quota.vacancies" @change="updateRuleQuotaPercentage(quota, index)" min="0" :max="totalQuota">

            <div v-if="selectedField.length > 0 && selectedField[index]">
                <div v-if="selectedField[index].fieldType === 'select'">
                    <div v-for="(option, index) in selectedField[index].fieldOptions" :key="index">
                        <input type="checkbox" :id="'option_' + index" :value="option" v-model="quota.eligibleValues" @change="autoSave">
                        <label :for="'option_' + index">{{ option }}</label>
                    </div>
                </div>

                <div v-if="selectedField[index].fieldType === 'checkbox'">
                    <div>
                        <input type="radio" id="trueOption" name="checkboxOption" value="true" v-model="quota.eligibleValues" @change="autoSave">
                        <label for="trueOption"><?= i::__('Sim') ?></label>
                    </div>
                    <div>
                        <input type="radio" id="falseOption" name="checkboxOption" value="false" v-model="quota.eligibleValues" @change="autoSave">
                        <label for="falseOption"><?= i::__('Não') ?></label>
                    </div>
                </div>
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

    <button @click="addConfig"><?= i::__('Adicionar categoria') ?></button>
</div>