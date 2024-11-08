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
<mc-modal v-if="needsTiebreaker && isMinervaGroup && enableExternalReviews" :title="`${evaluationName} - ${entity.number}`" classes="registration-results__modal">
    <template #default>
        <evaluation-qualification-detail :registration="entity"></evaluation-qualification-detail>
    </template>

    <template #button="modal">
        <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir detalhamento') ?></button>
    </template>
</mc-modal>

<div class="qualification-evaluation-form">
    <div v-for="section in sections" :key="section.id">
        <div v-if="showSectionAndCriterion(section)" class="qualification-evaluation-form__section field">
            <h3>{{ section.name }}</h3>
            <div class="field">
                <label><?php i::_e('Número máximo de critérios não eliminatórios: ') ?>{{ section.numberMaxNonEliminatory }}</label>
            </div>
            <div class="qualification-evaluation-form__criterion" v-for="crit in section.criteria" :key="crit.id">
                <div v-if="showSectionAndCriterion(crit)" class="field">
                    <div class="qualification-evaluation-form__criterion-title">
                        <div class="qualification-evaluation-form__criterion-title-fields">
                            <h3>{{ crit.name }}</h3>
                            <span v-if="crit.nonEliminatory === 'false'"><?php i::_e('*') ?></span>
                        </div>
                        <mc-popover openside="down-right" v-if="crit.description">
                            <template #button="popover">
                                <a @click="popover.toggle()"> <mc-icon name="help"></mc-icon> </a>
                            </template>
                            <template #default="{popover, close}">
                                <form @submit="$event.preventDefault()" class="entity-gallery__addNew--newGroup">
                                    <div class="grid-12">
                                        <div class="col-12">
                                            <a @click="close()"> <mc-icon name="close"></mc-icon> </a>
                                            <div class="field">
                                                <p>{{ crit.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </template>
                        </mc-popover>
                    </div>
                    <div class="field">
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

                            <div v-if="formData.data[crit.id]?.includes('invalid')" class="qualification-evaluation-form__criterion-options-reasons field">
                                <h4 class="col-12"><?php i::_e("Motivos de não atendimento") ?></h4>

                                <label v-for="option in crit.options" :key="option" class="col">
                                    <input type="checkbox" :value="option" :checked="formData.data[crit.id]?.includes(option)" :disabled="!isEditable" @change="updateOption(crit.id, option)" />
                                    {{ option }}
                                </label>

                                <label v-if="crit.otherReasonsOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                    <input type="checkbox" :name="'option-' + crit.id" value="others" :checked="formData.data[crit.id]?.includes('others')" :disabled="!isEditable" @change="toggleOthersOption(crit.id, $event)" />
                                <?php i::_e("Outras") ?>
                                </label>
                                <textarea v-if="formData.data[crit.id]?.includes('others')" v-model="formData.data[crit.id + '_reason']" :disabled="!isEditable" placeholder="<?= i::__('Descreva os motivos para inabilitação') ?>"></textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="field">
                <label><?php i::_e('Parecer') ?></label>
                <textarea v-model="formData.data[section.id]" :disabled="!isEditable" placeholder="<?= i::__('Digite o aparecer') ?>"></textarea>
                <label>
                    <?php i::_e('Resultado da seção:') ?> 
                    <span :class="sectionStatus(section.id) == text('Atende') ? 'qualification-enabled' : 'qualification-disabled'">
                        {{ sectionStatus(section.id) }}
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="qualification-evaluation-form__observation field">
        <label><?php i::_e('Observações') ?></label>
        <textarea v-model="formData.data.obs" :disabled="!isEditable"></textarea>
        <label>
            <?php i::_e('Status da avaliação:') ?> 
            <span :class="consolidatedResult == text('Atende') ? 'qualification-enabled' : 'qualification-disabled'">
                {{ consolidatedResult }}
            </span>
        </label>
    </div>
</div>