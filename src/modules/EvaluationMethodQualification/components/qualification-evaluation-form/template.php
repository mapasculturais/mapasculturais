<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    mc-icon
    mc-popover
')
?>
<div class="qualification-evaluation-form">
    <div class="scrollable-container scrollbar">
        <div v-for="section in sections" :key="section.id">
            <div v-if="showSectionAndCriterion(section)" class="qualification-evaluation-form__section field">
                <h3>{{ section.name }}</h3>
                <div class="field">
                    <label><?php i::_e('Número máximo de critérios não eliminatórios: ') ?>{{ section.numberMaxNonEliminatory }}</label>
                </div>
                <div class="qualification-evaluation-form__criterion" v-for="crit in section.criteria" :key="crit.id">
                    <div v-if="showSectionAndCriterion(crit)" class="field">
                        <div class="qualification-evaluation-form__criterion-title">
                            {{console.log(crit.nonEliminatory)}}
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
                                    <input type="radio" :name="'option-' + crit.id" value="Atende" :checked="formData.data[crit.id]?.includes('Atende')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                    <?php i::_e("Atende") ?>
                                </label>
                                <label class="qualification-evaluation-form__criterion-options-label">
                                    <input type="radio" :name="'option-' + crit.id" value="Não atende" :checked="formData.data[crit.id]?.includes('Não atende')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                    <?php i::_e("Não atende") ?>
                                </label>
                                <label v-if="crit.notApplyOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                    <input type="radio" :name="'option-' + crit.id" value="Não se aplica" :checked="formData.data[crit.id]?.includes('Não se aplica')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                    <?php i::_e("Não se aplica") ?>
                                </label>
                                <label v-if="crit.otherReasonsOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                    <input type="radio" :name="'option-' + crit.id" value="Outras" :checked="formData.data[crit.id]?.includes('Outras')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                    <?php i::_e("Outras") ?>
                                </label>

                                <div v-if="formData.data[crit.id]?.includes('Não atende')" class="qualification-evaluation-form__criterion-options-reasons field">
                                    <h4 class="col-12"><?php i::_e("Motivos para inabilitação") ?></h4>

                                    <label v-for="option in crit.options" :key="option" class="col">
                                        <input type="checkbox" :value="option" :checked="formData.data[crit.id]?.includes(option)" :disabled="!isEditable" @change="updateOption(crit.id, option)" />
                                        {{ option }}
                                    </label>
                                </div>
                            </div>

                            <textarea v-if="formData.data[crit.id].length > 0 && formData.data[crit.id].includes('Outras')" v-model="formData.data[crit.id + '_reason']" :disabled="!isEditable" placeholder="<?= i::__('Descreva os motivos para inabilitação') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label><?php i::_e('Parecer') ?></label>
                    <textarea v-model="formData.data[section.id]" :disabled="!isEditable" placeholder="<?= i::__('Digite o aparecer') ?>"></textarea>
                    <label>
                        <?php i::_e('Resultado da seção:') ?> 
                        <span :class="sectionStatus(section.id) == 'Atende' ? 'qualification-enabled' : 'qualification-disabled'">
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
                <span :class="consolidatedResult == 'Atende' ? 'qualification-enabled' : 'qualification-disabled'">
                    {{ consolidatedResult }}
                </span>
            </label>
        </div>
    </div>
    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>