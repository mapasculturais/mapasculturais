<?php
use MapasCulturais\i;
$this->import('
    modal
');
?>

<modal title="<?= i::__("Adicionar Fase de Avaliação") ?>">
    <template #default="modal">
        <div class="grid-12">
            <div class="col-12">
                <entity-field :entity="entity" prop="type"></entity-field>
            </div>
            <div class="col-12">
                <entity-field :entity="entity" prop="name"></entity-field>
            </div>
            <div class="col-6">
                <datepicker
                        :locale="locale"
                        :weekStart="0"
                        v-model="dateStart"
                        :format="dateFormat"
                        :dayNames="dayNames"
                        autoApply utc>
                </datepicker>
            </div>
            <div class="col-6">
                <datepicker
                        :locale="locale"
                        :weekStart="0"
                        v-model="dateEnd"
                        :format="dateFormat"
                        :dayNames="dayNames"
                        autoApply utc>
                </datepicker>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
        <button class="button button--primary" @click="save(modal)"><?= i::__("Adicionar") ?></button>
    </template>

    <template #button="modal">
        <a class="button button--primary w-100" href="#" @click="modal.open()"><?= i::__("Adicionar fase Avaliação") ?></a>
    </template>
</modal>
