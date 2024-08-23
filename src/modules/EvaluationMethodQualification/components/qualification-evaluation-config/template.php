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
');
?>

<div class="qualification-evaluation-config">
    <div v-if="entity.sections && entity.sections.length > 0">
        <div v-for="(section, index) in entity.sections" :key="index" class="qualification-evaluation-config__card">
            <div class="qualification-evaluation-config__header">
                <div class="title field">
                    <input v-if="editingSections[section.id]" type="text" v-model="section.name" ref="sectionNameInput" @blur="editSections(section.id);setSectionName();" placeholder="<?= i::esc_attr__('Nome seção') ?>">
                    <input class="bold" v-if="!editingSections[section.id]" type="text" :value="section.name" ref="sectionNameInput" @blur="editSections(section.id);" disabled placeholder="<?= i::esc_attr__('Nome seção') ?>">
                    <div class="title__buttons">
                        <button class="button button--icon button--text" @click="editSections(section.id)">
                            <mc-icon name="edit"></mc-icon>
                            <?php i::_e("Editar") ?>
                        </button>

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
            </div>

            <div class="qualification-evaluation-config__criterions" v-if="entity.criteria && entity.criteria.length > 0">
                <div class="criterions__title field">
                    <label><?php i::_e("Nome do critério") ?></label>
                </div>
                <div v-for="(criteria, index) in entity.criteria" :key="index">
                    <div class="criterion" v-if="criteria.sid == section.id">
                        <div class="field">
                            <small class="required" v-if="!criteria.name" ><i> <?= i::esc_attr__('Digite o nome critério') ?></i></small>
                            <input type="text" v-model="criteria.name" @keyup="save(1500)" placeholder="<?= i::esc_attr__('Nome do critério') ?>" ref="criteriaNameInput">
                        </div>
                        <div class="criterion__buttons">
                            <mc-modal v-if="criteria.name" :title="titleModal(criteria.name)" button-label="<?php i::_e("Configurar critério") ?>">
                                <div class="qualification-evaluation-config__modal-content grid-12 field">
                                    <label class="qualification-evaluation-config__modal-content__label col-12">
                                        <?php i::_e("Descrição do critério") ?>
                                        <textarea v-model="criteria.description" @blur="save()"></textarea>
                                    </label>
        
                                    <label class="qualification-evaluation-config__modal-content__label col-12">
                                        <?php i::_e("Opções ou motivos de inabilitação") ?>
                                        <textarea :value="optionsToString(criteria.options)" @blur="event => updateOptionsArray(criteria, event.target.value)" placeholder="<?= i::esc_attr__('As opções Habilitado e inabilitado já são definidas automaticamente pelo sistema') ?>"></textarea>
                                    </label>
        
                                    <label class="col-12">
                                        <input type="checkbox" v-model="criteria.notApplyOption" @change="notApplyChange(criteria)" />
                                        <?= i::__('Habilitar a opção Não se aplica?') ?>
                                    </label>
        
                                    <small class="field col-12">
                                        <label><?= i::__('Observações') ?></label>
                                        <ul>
                                            <li><?= i::__('As opções devem estar configuradas cada uma em uma linha') ?></li>
                                        </ul>
                                    </small>
                                </div>
                            </mc-modal>

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