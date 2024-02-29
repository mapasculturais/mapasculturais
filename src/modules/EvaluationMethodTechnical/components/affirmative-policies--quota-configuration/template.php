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

<<<<<<< HEAD
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
=======
    <div class ="affirmative-policies--quota-configuration__content" v-if="entity.quotaConfiguration && entity.quotaConfiguration.rules.length > 0">
        <div class="fields">
            <label class="field__title"><?= i::__('Percentual total das Cotas') ?>
                <div>
                    <input type="number" v-model="totalPercentage" @change="updateTotalQuotas" @blur="autoSave" min="0" max="100"> %
                </div>
            </label>

            <label class="field__title"><?= i::__('Número total das Cotas') ?>
                <input type="number" v-model="totalQuota" @change="updateQuotaPercentage" @blur="autoSave" min="0" :max="totalVacancies">
            </label>
        </div>

        <div class="affirmative-policies--quota-configuration__quota" v-for="(quota, index) in entity.quotaConfiguration.rules" :key="index">
            <div class="affirmative-policies--quota-configuration__column">
                <h5 class="field__title--semibold"><?= i::__('Cota') ?> {{index+1}}</h5>
                <select v-model="quota.fieldName" @change="selectField(quota.fieldName, index)">
                    <option class="select__selected-option" v-for="(item, index) in entity.opportunity.affirmativePoliciesEligibleFields" :value="item.fieldName">{{ '#' + item.id + ' ' + item.title }}</option>
                </select>
                
                <div class="field" v-if="selectedField.length > 0 && selectedField[index]">
                    <div class="field__column" v-if="selectedField[index].fieldType === 'select'">
                        <div v-for="(option, index) in selectedField[index].fieldOptions" :key="index">
                            <input class="input"type="checkbox" :id="'option_' + index" :value="option" v-model="quota.eligibleValues" @change="autoSave">
                            <label :for="'option_' + index">{{ option }}</label>
                        </div>
                    </div>
    
                    <div class="field__column" v-if="selectedField[index].fieldType === 'checkbox'">
                        <div>
                            <input class="input" type="radio" id="trueOption" name="checkboxOption" value="true" v-model="quota.eligibleValues" @change="autoSave">
                            <label for="trueOption"><?= i::__('Sim') ?></label>
                        </div>
                        <div>
                            <input class="input" type="radio" id="falseOption" name="checkboxOption" value="false" v-model="quota.eligibleValues" @change="autoSave">
                            <label for="falseOption"><?= i::__('Não') ?></label>
                        </div>
                    </div>
                </div>
>>>>>>> 5c1259e2756b27fc6a49dba97758aa3024bc36ca
            </div>

            <div class="quota__fields">
                <label class="field__title"><?= i::__('Porcentagem') ?>
                    <div> 
                        <input type="number" v-model="rulesPercentages[index]" @change="updateRuleQuotas(quota, index)" min="0" max="100"> %
                    </div>
                </label> 
            </div>

            <div class="quota__fields">
                <label class="field__title"><?= i::__('Número de Vagas') ?> 
                    <input type="number" v-model="quota.vacancies" @change="updateRuleQuotaPercentage(quota, index)" min="0" :max="totalQuota">
                </label>
            </div>
            <div class="quota__trash">
                <mc-confirm-button @confirm="removeConfig(index)">
                    <template #button="{open}">
                        <div class="field__trash button button--md button--text-danger button-icon">
                            <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                        </div>
                    </template>
                    <template #message="message">
                        <?= i::__('Deseja deletar a cota?') ?>
                    </template>
                </mc-confirm-button>
            </div>
        </div>
    </div>
    <div class="affirmative-policies--quota-configuration__footer">
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
</div>