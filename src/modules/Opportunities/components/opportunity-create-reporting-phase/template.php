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

<mc-modal title="<?= i::__("Adicionar etapa de prestação de informações") ?>" @open="createEntities()" @close="destroyEntities()" classes="-with-datepicker">
    <template #default="modal">
        <div class="grid-12">
            <p class="col-12"><?= i::__("Para criar a etapa de prestação de informações, preencha os campos abaixo.") ?></p>

            <div class="col-12 modal__title"><?= i::__("Prestação de informações") ?></div>
            <div class="col-12">
                <entity-field :entity="collectionPhase" prop="name" label="<?= i::__("Defina um título") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="collectionPhase" prop="registrationFrom" :min="minCollectionDate" label="<?= i::__("Data de início") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="collectionPhase" prop="registrationTo" :min="collectionPhase.evaluationFrom?._date" label="<?= i::__("Data final") ?>" hideRequired></entity-field>
            </div>
            <div class="col-12">
                <entity-field :entity="collectionPhase" prop="isFinalReportingPhase" label="<?= i::__('É fase final de prestação de informações?') ?>"></entity-field>
            </div>

            <div class="col-12 modal__title"><?= i::__("Avaliação contínua") ?></div>
            <div class="col-12">
                <entity-field :entity="evaluationPhase" prop="name" label="<?= i::__("Defina um título") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="evaluationPhase" prop="evaluationFrom" :min="collectionPhase.evaluationFrom?._date" label="<?= i::__("Data de início") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="evaluationPhase" prop="evaluationTo" :min="evaluationPhase.evaluationFrom?._date" label="<?= i::__("Data final") ?>" hideRequired></entity-field>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
        <button class="button button--primary" @click="save(modal)"><?= i::__("Adicionar") ?></button>
    </template>

    <template #button="modal">
        <button type="button" class="button button--primary w-100" @click="modal.open()">
            <?= i::__("Adicionar fase de prestação de informações") ?>
        </button>
    </template>
</mc-modal>