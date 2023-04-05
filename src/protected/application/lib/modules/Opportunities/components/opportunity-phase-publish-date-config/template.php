<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<div class="col-12">
    <div class="grid-12 opportunity-phase-publish-date-config">

        <template v-if="phase.publishedRegistrations">
            <div class="col-12">
                <confirm-button :message="text('despublicar')" @confirm="unpublishRegistration()">
                    <template #button="modal">
                        <button class="button button--text button--text-danger" @click="modal.open()">
                          <?= i::__("Despublicar") ?>
                        </button>
                    </template>
                </confirm-button>
            </div>
        </template>

        <template v-else>

            <div class="col-12" v-if="!hideDescription">
                <h5><?= i::__("A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.") ?></h5>
            </div>

            <div class="col-3" v-if="!hideButton">
                <confirm-button :message="text('confirmar_publicacao')" @confirm="publishRegistration()">
                    <template #button="modal">
                        <button class="button button--primary" @click="modal.open()">
                          <?= i::__("Publicar Resultados") ?>
                        </button>
                    </template>
                </confirm-button>
            </div>

            <template v-if="hideCheckbox && hideDatepicker && !!phase.publishTimestamp">
                <div class="col-4">
                    <h5>{{ msgPublishDateAuto }}</h5>
                </div>
            </template>

            <template v-else>
                <div class="col-4 sm:col-12" v-if="!hideDatepicker">
                    <entity-field :entity="phase" prop="publishTimestamp" :autosave="300" classes="col-6 sm:col-12" :min="getMinDate._date" :max="getMaxDate._date"></entity-field>
                </div>
                <div class="col-4" v-else-if="hideDatepicker && !!phase.publishTimestamp">
                    <h5 v-if="!!phase.publishTimestamp">{{ msgPublishDate }}</h5>
                </div>
                <div class="col-4" v-if="!hideCheckbox">
                    <entity-field :entity="phase" prop="autoPublish" :autosave="300" checkbox hideRequired hideLabel>
                        <template #checkboxLabel>
                          <?= i::__("Publicar resultados automaticamente"); ?>
                        </template>
                    </entity-field>
                </div>
                <div class="col-4" v-else>
                    <h5>{{ msgAutoPublish }}</h5>
                </div>
            </template>

        </template>

    </div>
</div>