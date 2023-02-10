<?php
use MapasCulturais\i;
$this->import('
    modal
');
?>

<modal title="<?= i::__("Adicionar fase de Coleta de Dados") ?>" @open="createEntity()" @close="destroyEntity()">
    <template #default="modal">
        {{ phase }}
        {{ opportunity }}
        <div class="grid-12">
            <div class="col-12">
                <entity-field :entity="phase" prop="name"></entity-field>
            </div>
            <div class="col-6">
<!--                <entity-field :entity="phase" prop="evaluationFrom"></entity-field>-->
            </div>
            <div class="col-6">
<!--                <entity-field :entity="phase" prop="evaluationTo" :min-date="evaluationFrom"></entity-field>-->
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
        <button class="button button--primary" @click="save(modal)"><?= i::__("Adicionar") ?></button>
    </template>

    <template #button="modal">
        <a class="button button--primary w-100" href="#" @click="modal.open()"><?= i::__("Adicionar fase de Coleta de Dados") ?></a>
    </template>
</modal>
