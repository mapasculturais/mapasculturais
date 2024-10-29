<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-confirm-button
    mc-icon
    mc-modal
    mc-tag-list
');
?>

<div class="qualification-evaluation-config">
    <div v-if="entity.sections && entity.sections.length > 0">
        <div v-for="(section, index) in entity.sections" :key="index" class="qualification-evaluation-config__card">
            <div class="qualification-evaluation-config__header">
                <div class="title field">
                    <input type="text" v-model="section.name" ref="sectionNameInput" @blur="editSections(section.id);setSectionName();" placeholder="<?= i::esc_attr__('Nome seção') ?>">
                    <div class="title__buttons">
                        <div class="field__trash">
                            <mc-confirm-button @confirm="delSection(section.id)">
                                <template #button="{open}">
                                    <button class="button button--delete button--icon" @click="open()">
                                        <mc-icon class="danger__color" name="trash"></mc-icon>
                                        <label class="semibold field__title"><?php i::_e("Excluir") ?></label>
                                    </button>
                                </template>
                                <template #message="message">
                                    <?= i::__('Deseja deletar a seção?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                </div>

                <div class="qualification-evaluation-config__section-filters">
                    <div v-if="entity.opportunity.registrationCategories.length > 1" class="field">
                        <label><?php i::_e("Selecione em quais categorias esta seção será utilizada:") ?></label>
                        <div v-for="category in entity.opportunity.registrationCategories" :key="category">
                            <label class="qualification-evaluation-config__filters-input">
                                <input
                                    type="checkbox"
                                    :value="category"
                                    :checked="isChecked(section, 'categories', category)"
                                    @change="updateSelections(section, 'categories', category, $event.target.checked)" />
                                {{category}}
                            </label>
                        </div>
                    </div>

                    <div v-if="entity.opportunity.registrationProponentTypes.length > 1" class="field">
                        <label><?php i::_e("Selecione em quais tipos de proponente esta seção será utilizada:") ?></label>
                        <div v-for="proponentType in entity.opportunity.registrationProponentTypes" :key="proponentType">
                            <label class="qualification-evaluation-config__filters-input">
                                <input
                                    type="checkbox"
                                    :value="proponentType"
                                    :checked="isChecked(section, 'proponentTypes', proponentType)"
                                    @change="updateSelections(section, 'proponentTypes', proponentType, $event.target.checked)" />
                                {{proponentType}}
                            </label>
                        </div>
                    </div>

                    <div v-if="entity.opportunity.registrationRanges.length > 1" class="field">
                        <label><?php i::_e("Selecione em quais faixa/linhas esta seção será utilizada:") ?></label>
                        <div v-for="range in entity.opportunity.registrationRanges" :key="range">
                            <label class="qualification-evaluation-config__filters-input">
                                <input
                                    type="checkbox"
                                    :value="range"
                                    :checked="isChecked(section, 'ranges', range)"
                                    @change="updateSelections(section, 'ranges', range, $event.target.checked)" />
                                {{range.label}}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qualification-evaluation-config__criterions" v-if="entity.criteria && entity.criteria.length > 0">
                <div class="criterions__title field">
                    <label><?php i::_e("Critérios de avaliação") ?></label>
                </div>
                <div v-for="(criteria, index) in entity.criteria" :key="index">
                    <div class="qualification-evaluation-config__criterion" v-if="criteria.sid == section.id">
                        <div class="criterion">
                            <div class="field">
                                <small class="required" v-if="!criteria.name"><i> <?= i::esc_attr__('Digite o nome critério') ?></i></small>
                                <input type="text" v-model="criteria.name" @keyup="save(1500)" placeholder="<?= i::esc_attr__('Nome do critério') ?>" ref="criteriaNameInput">
                            </div>
                            <div class="criterion__buttons">
                                <div class="field__trash">
                                    <mc-confirm-button @confirm="delCriteria(criteria.id)">
                                        <template #button="{open}">
                                            <button class="button button--md button--text-danger button-icon" @click="open()">
                                                <mc-icon class="danger__color" name="trash"></mc-icon>
                                            </button>
                                        </template>
                                        <template #message="message">
                                            <?= i::__('Deseja deletar o critério?') ?>
                                        </template>
                                    </mc-confirm-button>
                                </div>
                            </div> 
                        </div>
    
                        <div class="qualification-evaluation-config__config-criterion grid-12 field">
                            <div class="qualification-evaluation-config__config-criterion__config field">
                                <label class="col-12">
                                    <?php i::_e("Descrição do critério") ?>
                                    <textarea v-model="criteria.description" @blur="save()"></textarea>
                                </label>
        
                                <label class="col-12">
                                    <?php i::_e("Opções de inabilitação") ?>
                                    <div class="qualification-evaluation-config__config-criterion__input field">
                                        <input v-model="options" type="text" name="AddCriteriaOptions" @keyup.enter="updateOptions(criteria)" placeholder="<?= i::__("Escreva aqui as opções de inabilitação") ?>" />
                                        <button @click="updateOptions(criteria)" class="button button--primary button--icon" :class="!enabledButton() ? 'disabled' : ''">
                                            <mc-icon name="add"></mc-icon><label><?php i::_e("Adicionar opção") ?></label>
                                        </button>
                                    </div>
                                    <mc-tag-list v-if="criteria.options?.length" classes="opportunity__background" @click="save()" :tags="criteria.options" editable></mc-tag-list>
                                </label>
                            </div>

                            <div class="qualification-evaluation-config__config-criterion__checkboxes field">
                                <label class="col-12">
                                    <input type="checkbox" v-model="criteria.notApplyOption" @change="notApplyChange(criteria)" />
                                    <?= i::__('Habilitar a opção Não se aplica?') ?>
                                </label>
        
                                <label class="col-12">
                                    <input type="checkbox" v-model="criteria.otherReasonsOption" @change="otherReasonsChange(criteria)" />
                                    <?= i::__('Habilitar a opção Outros motivos para inabilitação?') ?>
                                </label>
                            </div>
                        </div>
    
                        <div class="qualification-evaluation-config__criteria-filters">
                            <div v-if="section.categories && criteria.sid === section.id && section.categories.length > 1" class="field">
                                <label><?php i::_e("Selecione em quais categorias este critério será utilizado:") ?></label>
                                <div v-for="category in section.categories" :key="category">
                                    <label class="qualification-evaluation-config__filters-input">
                                        <input
                                            type="checkbox"
                                            :value="category"
                                            :checked="isChecked(criteria, 'categories', category)"
                                            @change="updateSelections(criteria, 'categories', category, $event.target.checked)" />
                                        {{category}}
                                    </label>
                                </div>
                            </div>
    
                            <div v-if="section.proponentTypes && criteria.sid === section.id && section.proponentTypes.length > 1" class="field">
                                <label><?php i::_e("Selecione em quais tipos de proponente este critério será utilizado:") ?></label>
                                <div v-for="proponentType in section.proponentTypes" :key="proponentType">
                                    <label class="qualification-evaluation-config__filters-input">
                                        <input
                                            type="checkbox"
                                            :value="proponentType"
                                            :checked="isChecked(criteria, 'proponentTypes', proponentType)"
                                            @change="updateSelections(criteria, 'proponentTypes', proponentType, $event.target.checked)" />
                                        {{proponentType}}
                                    </label>
                                </div>
                            </div>
    
                            <div v-if="section.ranges && criteria.sid === section.id && section.ranges.length > 1" class="field">
                                <label><?php i::_e("Selecione em quais faixa/linhas este critério será utilizado:") ?></label>
                                <div v-for="range in section.ranges" :key="range">
                                    <label class="qualification-evaluation-config__filters-input">
                                        <input
                                            type="checkbox"
                                            :value="range"
                                            :checked="isChecked(criteria, 'ranges', range)"
                                            @change="updateSelections(criteria, 'ranges', range, $event.target.checked)" />
                                        {{range.label}}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="qualification-evaluation-config__add-criterion">
                <button @click="addCriteria(section.id)" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <label>
                        <?php i::_e("Adicionar critério") ?>
                    </label>
                </button>
            </div>
        </div>
    </div>

    <div class="qualification-evaluation-config__footer">
        <button @click="addSection" class="button button--primary button--icon">
            <mc-icon name="add"></mc-icon>
            <label>
                <?php i::_e("Adicionar seção de critérios de avaliação") ?>
            </label>
        </button>
    </div>
</div>