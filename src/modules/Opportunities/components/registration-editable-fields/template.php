<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
');
?>

<div class="registration-editable-fields">    
    <mc-modal title="<?= i::__("Campos editáveis") ?>" :classes="['registration-editable-fields__modal']">
        <template #default="modal">
            <div class="grid-12">
                <mc-alert class="col-12" v-if="openToEdit && !sent && afterDeadline" type="danger"> <?= i::__('O proponente perdeu o prazo de edição') ?> </mc-alert>
                <mc-alert class="col-12" v-if="sent" type="success"> <?= i::__('O proponente enviou a edição em: ') ?> 00/00/00 00:00 </mc-alert>
                <mc-alert class="col-12" v-if="openToEdit && !sent && !afterDeadline"type="warning"> <?= i::__('O proponente ainda não enviou as edições') ?> </mc-alert>
                
                <div class="registration-editable-fields__limit-field col-12">
                    <div class="field">
                        <label> <?php i::_e('Data limite de edição') ?></label>
                        <div class="datepicker">
                            <datepicker 
                                teleport
                                :weekStart="0"
                                v-model="editableUntil" 
                                :enableTimePicker='false' 
                                format="dd/mm/yy"
                                :dayNames="['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']"
                                multiCalendars multiCalendarsSolo autoApply utc></datepicker>
                        </div>
                    </div>
                </div>

                <div class="field col-12">
                    <label><?= i::__('Selecione os campos que ficarão disponíveis para edição') ?></label>
                    <div class="registration-editable-fields__fields">
                        <label v-for="field in fields" :for="field.id" class="registration-editable-fields__field">  
                            <input type="checkbox" v-model="selectedFields" :id="field.id" :value="field.ref" /> 
                            <p class="semibold">#{{field.id}} - {{field.title}}</p>
                        </label>
                    </div>
                </div>

            </div>
        </template>
        
        <template #actions="modal">
            <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
            <button v-if="canReopen" class="button button--primary" @click="reopen(modal)"><?= i::__("Reabrir edição") ?></button>
            <button class="button button--primary" @click="save(modal)"><?= i::__("Salvar") ?></button>
        </template>
        
        <template #button="modal">
            <slot :modal="modal">
                <button class="button button--primary" @click="modal.open()"><?= i::__("Configurar campos editáveis") ?></button>
            </slot>
        </template>
    </mc-modal>
</div>