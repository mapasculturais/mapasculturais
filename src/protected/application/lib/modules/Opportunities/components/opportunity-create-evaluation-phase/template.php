<?php
use MapasCulturais\i;
$this->import('
    modal
');
?>

<modal title="<?= i::__("Adicionar Fase de Avaliação") ?>" @open="createEntity()" @close="destroyEntity()">
    <template #default="modal">
        <div class="grid-12">
            <div class="col-12">
                <entity-field :entity="phase" prop="type" hideRequired></entity-field>
            </div>
            <div class="col-12">
                <entity-field :entity="phase" prop="name" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="phase" prop="evaluationFrom" hideRequired :min="minDate._date" :max="phase.evaluationTo?._date"></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="phase" prop="evaluationTo" hideRequired :min="phase.evaluationFrom?._date" :max="maxDate"></entity-field>
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
