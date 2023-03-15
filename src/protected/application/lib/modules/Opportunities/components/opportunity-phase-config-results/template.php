<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-link
');
?>

<mapas-card>
    <div class="config-phase grid-12">

        <template v-if="phase.publishedRegistrations">
            <div class="col-12">
                <button class="button button--text button--text-danger"><?= i::__("Despublicar") ?></button>
            </div>
        </template>
        <template v-else>
            <div class="col-6 sm:col-12">
                <entity-field :entity="phase" prop="publishTimestamp" classes="col-6 sm:col-12" :min="minDate?._date"></entity-field>
            </div>
            <div class="col-6 sm:col-12 phase-publish__auto">
                <input type="checkbox" v-model="phase.autoPublish"><?= i::__("Publicar resultados automaticamente"); ?>
            </div>
        </template>

        <div class="config-phase__line-bottom col-12 "></div>

        <div class="col-6 phase-publish__subscribers">
            <mc-link :entity='phase' route="registrations" icon="external" right-icon><?= i::__("Acessar lista de pessoas inscritas") ?></mc-link>
        </div>
        <div class="col-6 phase-publish__confirm">
            <button class="button" @click="phase.save()">
                <?= i::__("salvar") ?>
                <mc-icon name="upload"></mc-icon>
            </button>
            <confirm-button v-if="!isPublishLocked" :message="text('confirmar_publicacao')" @confirm="addPublishRegistrations()">
                <template #button="modal">
                    <button class="button button--primary" @click="modal.open()">
                      <?= i::__("Publicar Resultados") ?>
                    </button>
                </template>
            </confirm-button>
        </div>
    </div>
</mapas-card>