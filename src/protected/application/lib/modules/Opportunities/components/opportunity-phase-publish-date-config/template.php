<?php
use MapasCulturais\i;

$this->import('
    confirm-button
    entity-field
');
?>

<div class="col-12">
    <div class="grid-12 opportunity-phase-publish-date-config">
        <div v-if="phase.publishedRegistrations" class="published">
            <div class="col-4">
                <confirm-button :message="text('despublicar')" @confirm="unpublishRegistration()">
                    <template #button="modal">
                        <button class="button button--text button--text-danger" @click="modal.open()">
                            <?= i::__("Despublicar") ?>
                        </button>
                    </template>
                </confirm-button>
            </div>
            <div v-if="!!phase.publishTimestamp" class="col-4">
                <h5>{{ msgPublishDate }}</h5>
            </div>
        </div>

        <div v-if="!phase.publishedRegistrations" class="grid-12 col-12 notPublished">
            <div v-if="!hideButton && isPublished && firstPhase.status != 0" class="col-4">
                <confirm-button :message="text('confirmar_publicacao')" @confirm="publishRegistration()">
                    <template #button="modal">
                        <button class="button button--primary button-config" @click="modal.open()">
                            <?= i::__("Publicar Resultados") ?>
                        </button>
                    </template>
                </confirm-button>
            </div>

            <div v-if="!hideDescription" class="col-4">
                <h5><?= i::__("A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.") ?></h5>
            </div>

            <div v-if="hideCheckbox && hideDatepicker && !!phase.publishTimestamp" class="col-4">
                <h5>{{ msgPublishDateAuto }}</h5>
            </div>

            <div v-if="!(hideCheckbox && hideDatepicker && !!phase.publishTimestamp)" class="grid-12 col-4">
                <entity-field v-if="!hideDatepicker" :entity="phase" prop="publishTimestamp" :autosave="300" :min="minDate" :max="maxDate" classes="col-12 sm:col-12"></entity-field>
                <div class="col-10 msgpub-date" v-if="hideDatepicker && !!phase.publishTimestamp">
                    <h5>{{ msgPublishDate }}</h5>
                </div>
                <entity-field v-if="!hideCheckbox" :entity="phase" prop="autoPublish" :autosave="300" checkbox hideRequired hideLabel :disabled="!phase.publishTimestamp" classes="col-12">
                    <template #checkboxLabel>
                        <label class="col-10 checkbox"><?= i::__("Publicar resultados automaticamente"); ?></label>
                    </template>
                </entity-field>
                <div class="msg-auto-pub  col-10" v-else>
                    <h5>{{ msgAutoPublish }}</h5>
                </div>
            </div>

        </div>

    </div>
</div>