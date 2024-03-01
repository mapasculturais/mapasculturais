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
        <label>
            <?php i::_e("Nota máxima:") ?>
            {{ maxScore }}
        </label>

        <label>
            <?php i::_e("Nota de corte:") ?>
            <input type="number" v-model="entity.cutoffScore" min="0" @blur="autoSave()" placeholder="<?= i::esc_attr__('Nota de corte') ?>">
        </label>

        <div v-for="(section, index) in entity.sections" :key="index">
            <input v-if="editingSections[section.id]" type="text" v-model="section.name" @blur="sendConfigs();editSections(section.id);" placeholder="<?= i::esc_attr__('Nome sessão') ?>">
            <span v-else class="bold">{{section.name}}</span>

            <button @click="editSections(section.id)">
                <mc-icon name="edit"></mc-icon>
                <?php i::_e("Editar") ?>
            </button>

            <mc-confirm-button @confirm="delSection(section.id)">
                <template #button="{open}">
                    <div class="field__trash">
                        <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                        <label class="bold"><?php i::_e("Excluir") ?></label>
                    </div>
                </template>
                <template #message="message">
                    <?= i::__('Deseja deletar a sessão?') ?>
                </template>
            </mc-confirm-button>

            <div v-if="entity.criteria && entity.criteria.length > 0">
                <div v-for="(criteria, index) in entity.criteria" :key="index">
                    <div v-if="criteria.sid == section.id">
                        <input type="text" v-model="criteria.title" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Nome do critério') ?>">
                        <input type="number" v-model="criteria.max" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Pontuação máxima') ?>">
                        <input type="number" v-model="criteria.weight" @blur="sendConfigs" placeholder="<?= i::esc_attr__('Peso') ?>">
                        
                        <mc-confirm-button @confirm="delCriteria(criteria.id)">
                            <template #button="{open}">
                                <div class="field__trash">
                                    <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                                </div>
                            </template>
                            <template #message="message">
                                <?= i::__('Deseja deletar o critério?') ?>
                            </template>
                        </mc-confirm-button>
                    </div>
                </div>
            </div>

            <button @click="addCriteria(section.id)" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <label>
                    <?php i::_e("Adicionar critério") ?>
                </label>
            </button>
        </div>
    </div>

    <button @click="addSection" class="button button--primary button--icon">
        <mc-icon name="add"></mc-icon>
        <label>
            <?php i::_e("Adicionar nova sessão de Critérios") ?>
        </label>
    </button>
</div>