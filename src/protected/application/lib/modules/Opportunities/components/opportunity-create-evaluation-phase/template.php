<?php
use MapasCulturais\i;
$this->import('
    modal
');
?>

<modal title="<?= i::__("Adicionar Fase de Avaliação") ?>" @open="createEntity()" @close="destroyEntity()">
    <template #default="modal">
        {{ phase }}
        {{ opportunity }}
        <div class="grid-12">
            <div class="col-12">
                <entity-field :entity="phase" prop="type"></entity-field>
            </div>
            <div class="col-12">
                <entity-field :entity="phase" prop="name"></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="phase" prop="evaluationFrom" :max-date="evaluationTo"></entity-field>
            </div>
            <div class="col-6">
            <entity-field :entity="phase" prop="evaluationTo" :min-date="evaluationFrom"></entity-field>
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
