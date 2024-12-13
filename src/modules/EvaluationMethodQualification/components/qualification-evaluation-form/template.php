<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    evaluation-qualification-detail
    mc-icon
    mc-modal
    mc-popover
')
?>

<div class="qualification-evaluation-form" ref="formRoot">
    <h2 v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" class="needs-tiebreaker danger__background"><?= i::_e('Voto de minerva') ?></h2>
    <mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal" teleport="body">
        <template #default>
            <evaluation-qualification-detail :registration="entity"></evaluation-qualification-detail>
        </template>
    
        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Ver pareceres dos demais avaliadores') ?></button>
        </template>
    </mc-modal>
    <div v-for="section in sections" :key="section.id" class="qualification-evaluation-form__section-wrapper">
        <div v-if="showSectionAndCriterion(section)" class="qualification-evaluation-form__section field">
            <h3>{{ section.name }}</h3>
            <div v-if="section?.maxNonEliminatory" class="qualification-evaluation-form__section-non-eliminatory field">
                <label><?php i::_e('ATENÇÃO: Para ser habilitado, o proponente pode não atender até ') ?>{{ section.numberMaxNonEliminatory }}<?= i::__(' critérios') ?></label>
            </div>
            <template v-for="crit in section.criteria" :key="crit.id">
                <div v-if="showSectionAndCriterion(crit)" class="qualification-evaluation-form__criterion field">
                    <div class="qualification-evaluation-form__criterion-title">
                        <div class="qualification-evaluation-form__criterion-title-fields">
                            <h4>{{ crit.name }}</h4>
                            <span class="required" v-if="crit.nonEliminatory === 'false'">
                                *&nbsp;<?php i::_e('Critério eliminatório') ?>
                            </span>
                            <span class="required non-eliminatory" v-else>
                                *&nbsp;<?php i::_e('Critério não eliminatório') ?>
                            </span>
                        </div>
                        <mc-popover openside="down-right" v-if="crit.description">
                            <template #button="popover">
                                <a @click="popover.toggle()"> <mc-icon name="help"></mc-icon> </a>
                            </template>
                            <template #default="{popover, close}">
                                <form @submit="$event.preventDefault()" class="qualification-evaluation-form__popup">
                                    <div class="grid-12">
                                        <div class="col-12">
                                            <a class="qualification-evaluation-form__close-popup" @click="close()"> <mc-icon name="close"></mc-icon> </a>
                                            <div class="field">
                                                <p>{{ crit.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </template>
                        </mc-popover>
                    </div>
                    <div class="qualification-evaluation-form__criterion-options-wrapper field">
                        <div class="qualification-evaluation-form__criterion-options field">
                            <label class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="valid" :checked="formData.data[crit.id]?.includes('valid')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Atende") ?>
                            </label>
                            <label class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="invalid" :checked="formData.data[crit.id]?.includes('invalid')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Não atende") ?>
                            </label>
                            <label v-if="crit.notApplyOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="not-applicable" :checked="formData.data[crit.id]?.includes('not-applicable')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Não se aplica") ?>
                            </label>
                        </div>

                        <div v-if="formData.data[crit.id]?.includes('invalid') && (crit.options?.length > 0 || crit.otherReasonsOption === 'true')" class="qualification-evaluation-form__criterion-options-reasons field">
                            <h5 class="qualification-evaluation-form__criterion-options-reasons-title"><?php i::_e("Recomendação para atender ao critério") ?></h5>

                            <label class="qualification-evaluation-form__criterion-options-reasons-label" v-for="option in crit.options" :key="option">
                                <input type="checkbox" :value="option" :checked="formData.data[crit.id]?.includes(option)" :disabled="!isEditable" @change="updateOption(crit.id, option)" />
                                {{ option }}
                            </label>

                            <label v-if="crit.otherReasonsOption === 'true'" class="qualification-evaluation-form__criterion-options-reasons-other">
                                <input type="checkbox" :name="'option-' + crit.id" value="others" :checked="formData.data[crit.id]?.includes('others')" :disabled="!isEditable" @change="toggleOthersOption(crit.id, $event)" />
                            <?php i::_e("Outros") ?>
                            </label>
                            <textarea v-if="formData.data[crit.id]?.includes('others')" v-model="formData.data[crit.id + '_reason']" :disabled="!isEditable" placeholder="<?= i::__('Descreva as recomendações para atender ao critério') ?>"></textarea>
                        </div>
                    </div>
                </div>
            </template>

            <div class="field">
                <label><?php i::_e('Observações/parecer') ?></label>
                <textarea v-model="formData.data[section.id]" :disabled="!isEditable" placeholder="<?= i::__('Digite as observações/parecer') ?>"></textarea>
                <label>
                    <?php i::_e('Resultado da seção:') ?> 
                    <span :class="sectionClass(section.id)">
                        {{ sectionStatus(section.id) }}
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="qualification-evaluation-form__observation field">
        <h3><?php i::_e('Resultado da avaliação') ?> </h3>
        <label class="qualification-result">
            <span :class="{
                    'qualification-incomplete': consolidatedResult === text('Avaliação incompleta'),
                    'qualification-enabled': consolidatedResult === text('Habilitado'),
                    'qualification-disabled': consolidatedResult === text('Inabilitado')
                }">
                {{ consolidatedResult }}
            </span>
        </label>
        <label><?php i::_e('Observações/parecer final') ?></label>
        <textarea v-model="formData.data.obs" :disabled="!isEditable"></textarea>
    </div>
</div>