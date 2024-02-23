<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-select
');
?>

<div class="tiebreaker-criteria">
    <div class="tiebreaker-criteria__header">
        <h4 class="bold"><?= i::__('Configuração de Critérios de desempate') ?></h4> 
        <mc-icon name="info-full"></mc-icon>
    </div>

    <div class="tiebreaker-criteria__content">

        <div v-show="countCriteria > 0" v-for="criterion in criteria" class="tiebreaker-criteria__criterion grid-12 v-bottom">
            <div class="field col-6">
                <label>{{criterion.name}}</label>

                <mc-select placeholder="Critérios" @change-option="setCriterion($event, criterion.id)">
                    <option v-for="field in fields" :key="field.id" :value="field.id">{{field.title}}</option>
                    <option value="criteria"><?= i::__("Nota de um critério de avaliação") ?></option>
                    <option value="sectionCriteria"><?= i::__("Média de uma seção de critérios de avaliação") ?></option>
                </mc-select>
            </div>
            
            <div class="col-4">
                <div v-if="criterion.select == 'criteria'" class="tiebreaker-criteria__field">
                    <mc-select has-groups>
                        <optgroup v-for="section in sections" :label="section.name">
                            <option v-for="_criterion in section.criteria" :key="_criterion.id" value="option1"> {{_criterion.title}} </option>
                        </optgroup>                        
                    </mc-select>
                </div>

                <div v-if="criterion.select == 'sectionCriteria'" class="tiebreaker-criteria__field">
                    <mc-select>
                        <option v-for="section in sections" :key="section.id" value="option1"> {{section.name}} </option>
                    </mc-select>
                </div>

                <div v-if="criterion.selected" class="tiebreaker-criteria__field">
                    <mc-select v-if="criterion.selected.fieldType == 'boolean'">
                        <option value="marked"> <?= i::__('dar preferência para o valor marcado') ?> </option>
                        <option value="unmarked"> <?= i::__('dar preferência para o valor desmarcado') ?> </option>
                    </mc-select>

                    <mc-select v-if="criterion.selected.fieldType == 'number' || criterion.selected.fieldType == 'date'">
                        <option value="smallest"> <?= i::__('dar preferência ao menor valor') ?> </option>
                        <option value="largest"> <?= i::__('dar preferência ao maior valor') ?> </option>
                    </mc-select>
                </div>

            </div>

            <div class="col-2">
                <button class="button button--md button--text-danger button--icon" @click="unsetCriterion(criterion.id)">
                    <mc-icon name="trash"></mc-icon>
                </button>
            </div>

            <div v-if="criterionHasOptions(criterion)" class="col-12 field">
                <label><?= i::__('Dar preferência a:') ?></label>
                <div class="field field--horizontal">
                    <label v-for="option in criterion.selected.fieldOptions" class="field__checkbox">
                        <input type="checkbox" @click="change($event)" />
                        <slot>{{option}}</slot>
                    </label>
                </div>
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