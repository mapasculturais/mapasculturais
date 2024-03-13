<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-icon
    mc-select
');
?>

<div class="tiebreaker-criteria">
    <div v-if="!isActive" class="tiebreaker-criteria__active">
        <button class="button button--primary button--icon" @click="open()">
            <mc-icon name="add"></mc-icon>
            <?= i::__('Configurar critérios de desempate') ?>
        </button>

        <!-- <mc-icon name="info-full"></mc-icon> -->
    </div>

    <div v-if="isActive" class="tiebreaker-criteria__card">
        <div class="tiebreaker-criteria__header">
            <h4 class="bold"><?= i::__('Configuração de Critérios de desempate') ?></h4> 
            <!-- <mc-icon name="info-full"></mc-icon> -->
        </div>

        <div class="tiebreaker-criteria__content">
            <div v-show="criteria" v-for="criterion in criteria" class="tiebreaker-criteria__criterion">
                <div class="tiebreaker-criteria__column">
                    <div class="field tiebreaker-criteria__criterion-field">
                        <label>{{criterion.name}}</label>                    
                        <mc-select placeholder="Selecione um critério" :default-value="criterion.criterionType" @change-option="setCriterion($event, criterion.id)">
                            <option v-for="field in fields" :key="field.id" :value="field.fieldName">{{field.title}}</option>
                            <option value="criterion"><?= i::__("Nota de um critério de avaliação") ?></option>
                            <option value="sectionCriteria"><?= i::__("Média de uma seção de critérios de avaliação") ?></option>
                        </mc-select>
                    </div>

                    <div v-if="this.checkCriterionType(criterion, ['checkboxes', 'select'])" class="field">
                        <label><?= i::__('Dar preferência a:') ?></label>
                        <div class="field field--horizontal tiebreaker-criteria__group-field">
                            <label v-for="option in criterion.selected.fieldOptions">
                                <input type="checkbox" :true-value="[]" v-model="criterion.preferences" :value="option" />
                                <slot>{{option}}</slot>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="tiebreaker-criteria__column">
                    <template v-if="criterion.criterionType == 'criterion'">
                        <div class="field">
                            <label>&nbsp;</label>
                            <mc-select placeholder="<?= i::esc_attr__('Selecione um critério') ?>" v-model:default-value="criterion.preferences" has-groups>
                                <optgroup v-for="section in sections" :label="section.name">
                                    <option v-for="_criterion in section.criteria" :key="_criterion.id" :value="_criterion.id"> {{_criterion.title}} </option>
                                </optgroup>                        
                            </mc-select>
                        </div>
                    </template>

                    <template v-if="criterion.criterionType == 'sectionCriteria'">
                        <div class="field">
                            <label>&nbsp;</label>
                            <mc-select placeholder="<?= i::esc_attr__('Selecione uma seção') ?>" v-model:default-value="criterion.preferences">
                                <option v-for="section in sections" :key="section.id" :value="section.id"> {{section.name}} </option>
                            </mc-select>
                        </div>
                    </template>

                    <template v-if="checkCriterionType(criterion, ['boolean', 'checkbox'])" >
                        <div class="field">
                            <label>&nbsp;</label>
                            <mc-select placeholder="<?= i::esc_attr__('Selecione a preferência') ?>" v-model:default-value="criterion.preferences">
                                <option value="marked"> <?= i::__('dar preferência ao valor marcado') ?> </option>
                                <option value="unmarked"> <?= i::__('dar preferência ao valor desmarcado') ?> </option>
                            </mc-select>
                        </div>
                    </template>

                    <template v-if="checkCriterionType(criterion, ['number', 'date', 'currency'])" >
                        <div class="field">
                            <label>&nbsp;</label>
                            <mc-select placeholder="<?= i::esc_attr__('Selecione a preferência') ?>" v-model:default-value="criterion.preferences">
                                <option value="smallest"> <?= i::__('dar preferência ao menor valor') ?> </option>
                                <option value="largest"> <?= i::__('dar preferência ao maior valor') ?> </option>
                            </mc-select>
                        </div>
                    </template>
                </div>

                <div class="tiebreaker-criteria__column tiebreaker-criteria__column--center">
                    <mc-confirm-button @confirm="unsetCriterion(criterion.id)">
                        <template #button="modal">
                            <button class="button button--md button--text-danger button--icon" @click="modal.open()" >
                                <mc-icon name="trash"></mc-icon>
                            </button>
                        </template>
                        <template #message="message">
                            <div class="grid-12 h-center">
                                <p class="col-12 bold"><?= i::__('Você está certo que deseja excluir o') ?> {{criterion.name}}?</p>
                                <p class="col-12"><?= i::__('Os critérios restantes serão reordenados') ?></p>
                            </div>
                        </template>
                    </mc-confirm-button>
                </div>
            </div>        
        </div>

        <div class="tiebreaker-criteria__footer">
            <button class="button button--primary button--icon" @click="newCriterion()">
                <mc-icon name="add"></mc-icon>
                <?= i::__('Adicionar critérios') ?>
            </button>            
        </div>
    </div>
</div>