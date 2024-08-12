<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-alert
    mc-confirm-button
    mc-loading
');
?>

<div class="registration-editable-fields">    
    <mc-modal title="<?= i::__("Campos editáveis") ?>" :classes="['registration-editable-fields__modal']">
        <template #default="modal">
            <div class="grid-12">
                <mc-alert class="col-12" v-if="openToEdit && !sent && afterDeadline" type="danger"> <?= i::__('O proponente perdeu o prazo de edição') ?> </mc-alert>
                <mc-alert class="col-12" v-if="sent" type="success"> <?= i::__('O proponente enviou a edição em: ') ?> 
                    {{registration.editSentTimestamp.date('numeric year')}} 
                    <?= i::__('às') ?> 
                    {{registration.editSentTimestamp.time('numeric')}}
                </mc-alert>
                <mc-alert class="col-12" v-if="openToEdit && !sent && !afterDeadline"type="warning"> <?= i::__('O proponente ainda não enviou as edições') ?> </mc-alert>
                
                <div class="registration-editable-fields__limit-field col-12">
                    <div class="field">
                        <label> <?php i::_e('Data limite de edição') ?></label>
                        <div class="datepicker">
                            <entity-field :entity="registration" prop="editableUntil"></entity-field>
                        </div>
                    </div>
                </div>

                <div class="field col-12">
                    <label><?= i::__('Selecione os campos que ficarão disponíveis para edição') ?></label>

                    <label>  
                        <input type="checkbox" v-model="selectAll" @change="updateAllSelection"/> 
                        <span class="semibold"><?= i::__('Selecionar todos') ?></span>
                    </label>

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
            <template v-if="!processing">
                <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
                <mc-confirm-button v-if="canReopen" @confirm="reopen()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--secondary">
                            <?php i::_e('Reabrir edição') ?>
                        </button>
                    </template>

                    <template #message="message">
                        <?php i::_e('Deseja reabrir os campos para edição?') ?>
                    </template>
                </mc-confirm-button>
                <button class="button button--primary" @click="save(modal)"><?= i::__("Salvar") ?></button>
            </template>
            <mc-loading :condition="processing == 'saving'"><?php i::_e('Salvando') ?></mc-loading>
            <mc-loading :condition="processing == 'reopening'"><?php i::_e('Reabrindo') ?></mc-loading>
        </template>
        
        <template #button="modal">
            <slot :modal="modal">
                <button class="button button--primary" @click="modal.open()"><?= i::__("Configurar campos editáveis") ?></button>
            </slot>
        </template>
    </mc-modal>
</div>