<?php

use MapasCulturais\i;

$this->import('
    evaluation-actions
    mc-select
    mc-popover
')
?>
<div class="qualification-evaluation-form">
    <p class="semibold"><?php i::_e('Critérios de Avaliação') ?></p>
    <div class="qualification-evaluation-form__section field" v-for="section in sections" :key="section.id">
        <h3>{{ section.name }}</h3>
        <div class="qualification-evaluation-form__criterion" v-for="crit in section.criteria" :key="crit.id">
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
                <mc-select v-if="isEditable" v-model="formData.data[crit.id]" @change-option="updateSectionStatus(section.id, crit.id, $event)" :disabled="!isEditable">
                    <option v-if="crit.notApplyOption == 'true'" value="Não se aplica"><?php i::_e('Não se aplica') ?></option>
                    <option class="qualification-enabled" value="Habilitado"><?php i::_e('Habilitado') ?></option>
                    <option class="qualification-disabled" value="Inabilitado"><?php i::_e('Inabilitado') ?></option>
                    <option v-for="option in crit.options" :key="option" :value="option">{{ option }}</option>
                </mc-select>
                <input v-if="!isEditable" type="text" :value="formData.data[crit.id]" disabled>
            </div>
        </div>
        <label>
            <?php i::_e('Resultado da seção:') ?> 
            <span :class="sectionStatus(section.id) == 'Habilitado' ? 'qualification-enabled' : 'qualification-disabled'">
                {{ sectionStatus(section.id) }}
            </span>
        </label>
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