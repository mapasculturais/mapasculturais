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

<mc-modal title="<?= i::__("Adicionar fase de execução") ?>" @open="createEntities()" @close="destroyEntities()" classes="-with-datepicker">
    <template #default="modal">
        <div class="grid-12">
            <p class="col-12"><?= i::__("Para criar a fase de execução, preencha os dados abaixo.") ?></p>

            <div class="col-12 modal__title"><?= i::__("Fase de Execução") ?></div>
            <div class="col-12">
                <entity-field :entity="collectionPhase" prop="name" label="<?= i::esc_attr__("Defina um título") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="collectionPhase" prop="registrationFrom" label="<?= i::esc_attr__("Data de início") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="collectionPhase" prop="registrationTo" label="<?= i::esc_attr__("Data final") ?>" hideRequired></entity-field>
            </div>

            <div class="col-12 modal__title"><?= i::__("Avaliação dos pedidos") ?></div>
            <div class="col-12">
                <entity-field :entity="evaluationPhase" prop="name" label="<?= i::esc_attr__("Defina um título") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="evaluationPhase" prop="evaluationFrom" label="<?= i::esc_attr__("Data de início") ?>" hideRequired></entity-field>
            </div>
            <div class="col-6">
                <entity-field :entity="evaluationPhase" prop="evaluationTo" label="<?= i::esc_attr__("Data final") ?>" hideRequired></entity-field>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="modal.close()"><?= i::__("Cancelar") ?></button>
        <button class="button button--primary" @click="save(modal)"><?= i::__("Adicionar") ?></button>
    </template>

    <template #button="modal">
        <button type="button" class="button button--primary w-100" @click="modal.open()">
            <?= i::__("Adicionar fase de execução") ?>
        </button>
    </template>
</mc-modal>
