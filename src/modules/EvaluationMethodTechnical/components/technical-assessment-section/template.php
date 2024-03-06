<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-confirm-button
    mc-icon
');
?>

<div class="technical-assessment-section">
    <div v-if="entity.sections && entity.sections.length > 0">
        <div class="technical-assessment-section__fields">
            <div class="field">
                <label><?php i::_e("Nota máxima:") ?>
                    {{ maxScore }}
                </label>
            </div>
            <div class="field">
                <label><?php i::_e("Nota de corte:") ?>
                    <input class="field__input" type="number" v-model="entity.cutoffScore" min="0" @blur="autoSave()" placeholder="<?= i::esc_attr__('Nota de corte') ?>">
                </label>
            </div>
        </div>

        <div class="technical-assessment-section__card" v-for="(section, index) in entity.sections" :key="index">
            <div class="technical-assessment-section__header">
                <div class="title">
                    <input v-if="editingSections[section.id]" type="text" v-model="section.name" @blur="sendConfigs();editSections(section.id);" placeholder="<?= i::esc_attr__('Nome sessão') ?>">
                    <span v-else class="bold">{{section.name}}</span>
                    <div class="title__buttons">
                        <button class="button button-icon" @click="editSections(section.id)">
                            <mc-icon name="edit"></mc-icon>
                            <?php i::_e("Editar") ?>
                        </button>

                        <div class="field__trash">
                            <mc-confirm-button @confirm="delSection(section.id)">
                                <template #button="{open}">
                                    <button class="button button-icon button--text-danger">
                                        <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                                        <label class="semibold field__title"><?php i::_e("Excluir") ?></label>
                                    </button>
                                </template>
                                <template #message="message">
                                    <?= i::__('Deseja deletar a sessão?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="technical-assessment-section__criterions" v-if="entity.criteria && entity.criteria.length > 0">
                <div v-for="(criteria, index) in entity.criteria" :key="index">
                    <div class="criterion" v-if="criteria.sid == section.id">
                        <div class="criterion-fields">
                            <input type="text" v-model="criteria.title" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Nome do critério') ?>">
                            <input type="number" v-model="criteria.max" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Pontuação máxima') ?>">
                            <input type="number" v-model="criteria.weight" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Peso') ?>">
                        </div>
                        <div class="field__trash">
                            <mc-confirm-button  @confirm="delCriteria(criteria.id)">
                                <template #button="{open}">
                                    <button class="button button--md button--text-danger button-icon">
                                        <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                                    </button>
                                </template>
                                <template #message="message">
                                    <?= i::__('Deseja deletar o critério?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="card--add-criterion">
                <button @click="addCriteria(section.id)" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <label>
                        <?php i::_e("Adicionar critério") ?>
                    </label>
                </button>
            </div>
        </div>
    </div>

    <div class="technical-assessment-section__footer">
        <button @click="addSection" class="button button--primary button--icon">
            <mc-icon name="add"></mc-icon>
            <label>
                <?php i::_e("Adicionar nova sessão de Critérios") ?>
            </label>
        </button>
    </div>
</div>