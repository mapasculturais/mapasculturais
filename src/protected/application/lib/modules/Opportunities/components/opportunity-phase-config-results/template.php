<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<mapas-card>
    <div class="config-phase grid-12">

        <template v-if="entity.publishedRegistrations">
            <div class="col-12">
                <button class="button button--text button--text-danger"><?= i::__("Despublicar") ?></button>
            </div>
        </template>
        <template v-else>
            <div class="col-6 sm:col-12">
                <entity-field :entity="entity" prop="publishTimestamp" classes="col-6 sm:col-12" :min="getMinDate(entity.__objectType, currentIndex)" :max="getMaxDate(entity.__objectType, currentIndex)"></entity-field>
            </div>
            <div class="col-6 sm:col-12 phase-publish__auto">
                <input type="checkbox" v-model="entity.autoPublish"><?= i::__("Publicar resultados automaticamente"); ?>
            </div>
        </template>

        <div class="config-phase__line-bottom col-12 "></div>

        <div class="col-6 phase-publish__subscribers">
            <button class="button button--text"><?= i::__("Acessar lista de pessoas inscritas") ?><mc-icon name="upload"></mc-icon></button>
        </div>
        <div class="col-6 phase-publish__confirm">
            <confirm-button :message="text('confirmar_publicacao')" @confirm="addPublishRegistrations(item)">
                <template #button="modal">
                    <button v-if='isBlockedPublish(index)' class="button" disabled>
                      <?= i::__("Publicar Resultados") ?>
                        <mc-icon name="upload"></mc-icon>
                    </button>
                    <button v-else class="button button--primary" @click="modal.open">
                      <?= i::__("Publicar Resultados") ?>
                        <mc-icon name="upload"></mc-icon>
                    </button>
                </template>
            </confirm-button>
        </div>
    </div>
</mapas-card>