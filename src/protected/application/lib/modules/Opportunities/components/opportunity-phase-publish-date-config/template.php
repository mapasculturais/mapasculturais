<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<div class="col-12">
    <div class="grid-12">

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

            <!-- BUTTON -->
            <div class="col-3">
                <confirm-button :message="text('confirmar_publicacao')" @confirm="publishRegistration()">
                    <template #button="modal">
                        <button v-if="!isBlockPublish" class="button button--primary" @click="modal.open()">
                          <?= i::__("Publicar Resultados") ?>
                        </button>
                        <button v-else class="button" disabled>
                          <?= i::__("Publicar Resultados") ?>
                        </button>
                    </template>
                </confirm-button>
            </div>

            <!-- DESCRIPTION -->
            <div class="col-6" v-if="!hideDescription">
                <h5><?= i::__("A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.") ?></h5>
            </div>

            <!-- CHECKBOX -->
            <div class="col-3 field" v-if="!hideCheckbox">
                <entity-field :entity="phase" prop="autoPublish" :autosave="300" checkbox hideRequired hideLabel>
                    <template #checkboxLabel>
                      <?= i::__("Publicar resultados automaticamente"); ?>
                    </template>
                </entity-field>
            </div>
            <div class="col-3 field" v-else>
                <p><?= i::__("Os resultados serão publicados automaticamente"); ?></p>
            </div>

        </template>

    </div>
</div>