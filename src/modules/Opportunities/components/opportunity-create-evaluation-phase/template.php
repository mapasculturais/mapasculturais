<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
$this->import('
    entity-field
    mc-modal
');
?>

<mc-modal title="<?= i::__("Adicionar fase de avaliação") ?>" @open="createEntity()" @close="destroyEntity()" classes="-with-datepicker">
    <template #default="modal">
        <div class="grid-12">
            <div class="col-12">
                <entity-field :entity="phase" prop="type" hideRequired></entity-field>
            </div>
            <div class="col-12">
                <entity-field :entity="phase" prop="name" hideRequired :placeholder="placeholder"></entity-field>
            </div>
            <div class="col-6" v-if="!isContinuousFlow">
                <entity-field :entity="phase" prop="evaluationFrom" hideRequired :min="minDate" :max="phase.evaluationTo?._date"></entity-field>
            </div>
            <div class="col-6" v-if="!isContinuousFlow">
                <entity-field :entity="phase" prop="evaluationTo" hideRequired :min="phase.evaluationFrom?._date" :max="maxDate"></entity-field>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
        <button class="button button--primary" @click="save(modal)"><?= i::__("Adicionar") ?></button>
    </template>

    <template #button="modal">
        <a class="button button--primary w-100" href="javascript:void(0)" @click="modal.open()"><?= i::__("Adicionar fase de avaliação") ?></a>
    </template>
</mc-modal>
