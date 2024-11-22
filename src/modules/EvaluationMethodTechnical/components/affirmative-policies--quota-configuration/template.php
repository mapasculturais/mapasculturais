<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    entity-field
    mc-confirm-button
    mc-icon
');
?>

<mc-alert v-if="firstPhase.vacancies <= 0" type="warning" class="entity-owner-pending">
    <div>
        <?= i::__('O número de vagas do edital não foi configurado. Para definir a configuração das cotas, é necessário primeiro estabelecer esse valor.') ?>
    </div>
</mc-alert>

<div class="affirmative-policies--quota-configuration">
    <div v-if="!isActive" class="affirmative-policies--quota-configuration__activate">
        <button @click="addConfig();" class="button button--primary button--icon" :class="{'disabled' : firstPhase.vacancies <= 0}">
            <mc-icon name="add"></mc-icon>
            <?php i::_e("Configurar cotas") ?>
        </button>
    </div>

    <div v-if="firstPhase.vacancies > 0">
        <div v-if="isActive" class="affirmative-policies--quota-configuration__header">
            <h4 class="bold"><?= i::__('Configuração das cotas') ?></h4>

            <div class="affirmative-policies--quota-configuration__header-fields">
                <label v-if="totalVacancies > 0" class="field__title">
                    <?= i::__('Percentual total para cotistas:') ?>
                    {{totalPercentage}}%
                </label>

                <label class="field__title">
                    <?= i::__('Vagas para cotistas:') ?>
                    {{totalQuota}}
                </label>
            </div>

            <mc-alert v-if="totalQuota > firstPhase.vacancies" type="warning" class="entity-owner-pending">
                <div>
                    <?= i::__('O número de vagas reservadas para cotistas excede o total de vagas disponíveis no edita.') ?>
                </div>
            </mc-alert>

            <hr style="width: 100%;">
            <entity-field :entity="phase.opportunity.parent ?? phase.opportunity" prop="considerQuotasInGeneralList" :autosave="3000"></entity-field>
        </div>

        <div v-if="isActive" v-for="(quota, index) in phase.quotaConfiguration.rules" :key="index" class="affirmative-policies--quota-configuration__card">
            <div class="affirmative-policies--quota-configuration__card-header">

                <div class="field">
                    <label><?= i::__('Descrição da cota:') ?></label>
                    <input type="text" v-model="quota.title">
                </div>

                <div v-if="totalVacancies > 0" class="field">
                    <label><?= i::__('Porcentagem') ?></label>
                    <span class="affirmative-policies--quota-configuration__quota-field-sufix">
                        <input type="number" v-model="quota.percentage" @change="updateRuleQuotas(quota)" min="0" max="100"> %
                    </span>
                </div>

                <div class="field">
                    <label><?= i::__('Número de vagas') ?></label>
                    <input type="number" v-model="quota.vacancies" @change="updateRuleQuotaPercentage(quota)" min="0">
                </div>

                <mc-confirm-button @confirm="removeConfig(index)">
                    <template #button="{open}">
                        <button class="button button--sm button--text-danger button--icon affirmative-policies--quota-configuration__quota-delete" @click="open()">
                            <mc-icon class="danger__color" name="trash"></mc-icon>
                            <?= i::__('Excluir') ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?= i::__('Deseja deletar a cota?') ?>
                    </template>
                </mc-confirm-button>

            </div>

            <div class="affirmative-policies--quota-configuration__card-content">

                <div v-for="proponentType in proponentTypes" :key="proponentType" class="affirmative-policies--quota-configuration__quota-field">
                    <div class="affirmative-policies--quota-configuration__field">
                        <div class="field">
                            <label v-if="proponentType === 'default'"><?= i::__('Selecione o campo que define a cota') ?></label>
                            <label v-else><?= i::__('Selecione o campo que define a cota para {{proponentType}}') ?></label>
                            <mc-select v-model:default-value="getQuotaField(proponentType,quota).fieldName" placeholder="<?= i::esc_attr__('Selecione um campo') ?>" show-filter>
                                <option v-for="(item, index) in filteredOptions(proponentType)" :value="item.fieldName">{{ '#' + item.id + ' ' + item.title }}</option>
                            </mc-select>
                        </div>
                    </div>

                    <div v-if="getFieldType(getQuotaField(proponentType,quota)) === 'select' || getFieldType(getQuotaField(proponentType,quota)) === 'multiselect' || getFieldType(getQuotaField(proponentType,quota)) === 'checkboxes'" class="field">
                        <label><?= i::__('Dar preferência a') ?>:</label>

                        <div class="field field--horizontal">
                            <label v-for="option in getFieldOptions(getQuotaField(proponentType,quota))">
                                <input type="checkbox" :value="optionValue(option)" :true-value="[]" v-model="getQuotaField(proponentType,quota).eligibleValues" />
                                <span>{{optionLabel(option)}}</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="getFieldType(getQuotaField(proponentType,quota)) === 'checkbox' || getFieldType(getQuotaField(proponentType,quota)) === 'boolean'" class="field">
                        <label><?= i::__('Dar preferência a') ?>:</label>

                        <div class="field field--horizontal">
                            <label>
                                <input type="radio" :name="quota.fieldName + ':' + index" :value="true" v-model="getQuotaField(proponentType,quota).eligibleValues">
                                <span><?= i::__('Sim / Marcado') ?></span>
                            </label>

                            <label>
                                <input type="radio" :name="quota.fieldName + ':' + index" :value="false" v-model="getQuotaField(proponentType,quota).eligibleValues">
                                <span><?= i::__('Não / Desmarcado') ?></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="isActive" class="affirmative-policies--quota-configuration__add-category">
        <button @click="addConfig();" class="button button--primary button--icon" :class="{'disabled' : firstPhase.vacancies <= 0}">
            <mc-icon name="add"></mc-icon>
            <?php i::_e("Adicionar nova configuração de cota") ?>
        </button>
    </div>
</div>