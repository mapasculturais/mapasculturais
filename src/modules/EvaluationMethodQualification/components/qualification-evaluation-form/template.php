<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    mc-icon
    mc-popover
')
?>
<div class="qualification-evaluation-form">
    <p class="semibold"><?php i::_e('Critérios de Avaliação') ?></p>
    <div v-for="section in sections" :key="section.id">
        <div v-if="showSectionAndCriterion(section)" class="qualification-evaluation-form__section field">
            <h3>{{ section.name }}</h3>
            <div class="qualification-evaluation-form__criterion" v-for="crit in section.criteria" :key="crit.id">
                <div v-if="showSectionAndCriterion(crit)" class="field">
                    <div class="qualification-evaluation-form__criterion-title">
                        <label>{{ crit.name }}</label>
                        <mc-popover openside="down-right">
                            <template #button="popover">
                                <a @click="popover.toggle()"> <mc-icon name="info"></mc-icon> </a>
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
                                <input type="radio" :name="'option-' + crit.id" value="Habilitado" :checked="formData.data[crit.id]?.includes('Habilitado')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Habilitado") ?>
                            </label>
                            <label class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="Inabilitado" :checked="formData.data[crit.id]?.includes('Inabilitado')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Inabilitado") ?>
                            </label>
                            <label v-if="crit.notApplyOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="Não se aplica" :checked="formData.data[crit.id]?.includes('Não se aplica')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Não se aplica") ?>
                            </label>
                            <label v-if="crit.otherReasonsOption === 'true'" class="qualification-evaluation-form__criterion-options-label">
                                <input type="radio" :name="'option-' + crit.id" value="Outras" :checked="formData.data[crit.id]?.includes('Outras')" :disabled="!isEditable" @change="updateSectionStatus(section.id, crit.id, $event)" />
                                <?php i::_e("Outras") ?>
                            </label>

                            <div v-if="formData.data[crit.id]?.includes('Inabilitado')" class="qualification-evaluation-form__criterion-options-reasons field">
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
                <label><?php i::_e('Parecer') ?></label>
                <textarea v-model="formData.data[section.id]" :disabled="!isEditable" placeholder="<?= i::__('Digite o aparecer') ?>"></textarea>
            <label>
                <?php i::_e('Resultado da seção:') ?> 
                <span :class="sectionStatus(section.id) == 'Habilitado' ? 'qualification-enabled' : 'qualification-disabled'">
                    {{ sectionStatus(section.id) }}
                </span>
            </label>
        </div>
    </div>
    <div class="qualification-evaluation-form__observation field">
        <p><?php i::_e('Observações') ?></p>
        <textarea v-model="formData.data.obs" :disabled="!isEditable"></textarea>
        <label>
            <?php i::_e('Status da avaliação:') ?> 
            <span :class="consolidatedResult == 'Habilitado' ? 'qualification-enabled' : 'qualification-disabled'">
                {{ consolidatedResult }}
            </span>
        </label>
    </div>
    <evaluation-actions :formData="formData" :entity="entity" :validateErrors='validateErrors'></evaluation-actions>
</div>