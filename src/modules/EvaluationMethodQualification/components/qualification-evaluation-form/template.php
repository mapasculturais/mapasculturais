<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    mc-icon
    mc-multiselect
    mc-popover
    mc-tag-list
')
?>
<div class="qualification-evaluation-form">
    <p class="semibold"><?php i::_e('Critérios de Avaliação') ?></p>
    <div class="qualification-evaluation-form__section field" v-for="section in sections" :key="section.id">
        <div v-if="showSectionAndCriterion(section)" class="field">
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
                    <div>
                        <template v-if="isEditable">
                            <mc-multiselect :model="formData.data[crit.id]" :items="combinedOptions(crit)" #default="{popover, setFilter}" @selected="updateSectionStatus(section.id)">
                                <button class="button button--rounded button--sm button--icon button--primary" @click="popover.toggle()" >
                                    <?php i::_e("Escolher opções") ?>
                                    <mc-icon name="add"></mc-icon>
                                </button>
                            </mc-multiselect>

                            <mc-tag-list :tags="formData.data[crit.id]" classes="opportunity__background" @remove="updateSectionStatus(section.id, crit.id, $event)" editable></mc-tag-list>
                        </template>

                        <mc-tag-list v-if="!isEditable" :tags="formData.data[crit.id]" classes="opportunity__background"></mc-tag-list>

                        <textarea v-if="formData.data[crit.id].length > 0 && formData.data[crit.id].includes('Outras')" v-model="formData.data[crit.id + '_reason']" placeholder="<?= i::__('Descreva os motivos para inabilitação') ?>"></textarea>
                    </div>
                </div>
            </div>
                <label><?php i::_e('Parecer') ?></label>
                <input v-model="formData.data[section.id]" type="text">
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