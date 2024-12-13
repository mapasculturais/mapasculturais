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
    mc-toggle
    opportunity-registration-filter-configuration
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
                    <div class="field qualification-evaluation-config__non-eliminatory">
                        <mc-toggle
                            :modelValue="section.maxNonEliminatory" 
                            @update:modelValue="enableMaxNonEliminatory($event, section)"
                            label="<?= i::__('Limitar número máximo de critérios não eliminatórios por seção') ?>"
                        />
                        <input v-if="section.maxNonEliminatory" v-model="section.numberMaxNonEliminatory" type="number" @change="save()"/>
                    </div>
                    
                    <mc-toggle 
                        :modelValue="section.showFilters"
                        @update:modelValue="enableFilterConfigSection($event, section)"
                        label="<?= i::__('Configurar filtro') ?>"
                        >
                    </mc-toggle>
                    <opportunity-registration-filter-configuration
                        v-if="section.showFilters"
                        :entity="entity"
                        v-model:default-value="section"
                        :excludeFields="['id', 'name', 'showFilters', 'maxNonEliminatory', 'numberMaxNonEliminatory']"
                        titleModal="<?= i::__('Configuração de filtros da seção') ?>"
                        is-section
                        ></opportunity-registration-filter-configuration>

                        <mc-toggle
                            :modelValue="section.requiredSectionObservation" 
                            @update:modelValue="enableRequiredSectionObservation($event, section)"
                            label="<?= i::__('Parecer da seção obrigatório') ?>"
                        />
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
                                    <?php i::_e("Opções de não atendimento do critério") ?>
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
                                    <?= i::__('Habilitar a opção Outros motivos para não atendimento?') ?>
                                </label>

                                <label class="col-12">
                                    <input type="checkbox" v-model="criteria.nonEliminatory" @change="nonEliminatoryChange(criteria)" />
                                    <?= i::__('Não eliminatório') ?>
                                </label>
                            </div>
                        </div>
    
                        <div class="qualification-evaluation-config__criteria-filters">
                            <mc-toggle 
                                :modelValue="criteria.showFilters"
                                @update:modelValue="enableFilterConfigCriteria($event, criteria)"
                                label="<?= i::__('Configurar filtro') ?>"
                                >
                            </mc-toggle>
                            <opportunity-registration-filter-configuration
                                v-if="criteria.showFilters"
                                :entity="entity"
                                v-model:default-value="criteria"
                                :excludeFields="['id', 'name', 'showFilters', 'options', 'notApplyOption', 'otherReasonsOption', 'sid', 'weight', 'description', 'nonEliminatory']"
                                titleModal="<?= i::__('Configuração de filtros do critério') ?>"
                                is-criterion
                            ></opportunity-registration-filter-configuration>   
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