<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
    mc-modal
    mc-select
');
?>


<div class="affirmative-policy--bonus-config">
    <div class="affirmative-policy--bonus-config__card" v-if="entity.pointReward && entity.pointReward.length || entity.isActivePointReward">
        <div class="affirmative-policy--bonus-config__header">
            <h4 class="bold"><?= i::__('Configuração do bônus de pontuação') ?></h4>
            <div class="affirmative-policy--bonus-config__field field">
                <label>
                    <?= i::__('Percentual total de indução:') ?>
                </label>
                <span>
                    <input type="number" v-model="entity.pointRewardRoof" @change="autoSave(true)" min="0" max="100" /> %
                </span>
            </div>
        </div>

        <div class="affirmative-policy--bonus-config__quota" v-if="entity.pointReward" v-for="(quota, index) in entity.pointReward" :key="index">
            <div class="affirmative-policy--bonus-config__column">
                <h5 class="field__title--semibold"><?= i::__('Percentual') ?> {{index+1}}</h5>


                <mc-select @change-option="setFieldName($event, quota)" :default-value="quota.field" placeholder="<?= i::esc_attr__('Selecione um campo') ?>" show-filter>
                    <option v-for="(item, index) in entity.opportunity.affirmativePoliciesEligibleFields" :value="item.id">{{ '#' + item.id + ' - ' + item.title }}</option>
                </mc-select>


                <div class="affirmative-policy--bonus-config__fields" v-if="hasField(quota)">
                    <div class="field">
                        <label>
                            <?= i::__('Tipo:') ?>
                        </label>
                    </div>
                    <div class="field affirmative-policy--bonus-config__row" v-if="getFieldType(quota) === 'select' || getFieldType(quota) === 'multiselect'">
                        <label v-for="option in getFieldOptions(quota)">
                            <input class="input" type="checkbox" :value="optionValue(option)" v-model="quota.value[option]" @change="checkboxUpdate($event,quota)">
                            {{optionLabel(option)}}
                        </label>
                    </div>

                <div v-if="getFieldType(quota) === 'checkboxes'" class="field">
                    <div class="field field--horizontal">
                        <label v-for="option in getFieldOptions(quota)">
                            <input type="checkbox" :value="option" :true-value="[]" v-model="quota.eligibleValues" />
                            <span>{{option}}</span>
                        </label>
                    </div>
                </div>
                    
                    <div class="field__column" v-if="getFieldType(quota) === 'checkbox' || getFieldType(quota) === 'boolean'">
                        <label>

                            <input class="input" type="radio" :name="quota.fieldName + ':' + index" :value="true" v-model="quota.value">
                            <?= i::__('Sim / Marcado') ?>
                        </label>
                        <label>
                            <input class="input" type="radio" :name="quota.fieldName + ':' + index" :value="false" v-model="quota.value">
                            <?= i::__('Não / Desmarcado') ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="affirmative-policy--bonus-config__column">
                <label class="field"><?= i::__('Porcentagem') ?>
                    <div>
                        <input type="number" v-model="quota.fieldPercent" min="0" max="100"> %
                    </div>
                </label>
            </div>


            <div class="quota__trash">
                <mc-confirm-button @confirm="removeConfig(index)">
                    <template #button="{open}">
                        <button class="field__trash button button--md button--text-danger button-icon" @click="open()">
                            <mc-icon class="danger__color" name="trash" ></mc-icon>
                        </button>
                    </template>
                    <template #message="message">
                        <?= i::__('Deseja remover esta configuração de bôuns?') ?>
                    </template>
                </mc-confirm-button>
            </div>
        </div>


    </div>
    <div class="affirmative-policy--bonus-config__footer">
        <button @click="addConfig();" class="button button--primary button--icon">
            <mc-icon name="add"></mc-icon>
            <label v-if="entity.pointReward && entity.pointReward.length > 0 || entity.isActivePointReward">
                <?php i::_e("Adicionar categoria") ?>
            </label>
            <label v-else>
                <?php i::_e("Configurar bônus de pontuação") ?>
            </label>
        </button>
    </div>
</div>