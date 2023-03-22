<?php
use MapasCulturais\i;

$this->import('confirm-button')
?>

<mapas-card>
    <div class="grid-12 opportunity-phase-list-data-collection">
        <div class="col-12">
            <p><?= i::__("Quantidade inscrições:") ?> <strong>xxx inscrições</strong></p>
        </div>
        <div class="col-4 sm:col-12 subscribe_prev_phase">
            <!-- TO DO -->
        </div>
        <div class="col-8 sm:col-12 subscribe_prev_phase">
            <p><strong><?= i::__("Ao trazer as inscrições, você garante que apenas participantes classificados na fase anterior sigam para a póxima fase.") ?></strong></p>
        </div>
        <div class="col-12 opportunity-phase-list-data-collection_action--center">
            <mc-link :entity="entity" class="opportunity-phase-list-data-collection_action--button" icon="external" route="registrations" right-icon>
              <?= i::__("Lista de inscrições da fase") ?>
            </mc-link>
        </div>
        <div class="config-phase__line-bottom col-12"></div>
        <div class="col-3">
            <confirm-button :message="text('confirmar_publicacao')" @confirm="addPublishRegistrations()">
                <template #button="modal">
                    <button class="button button--primary" @click="modal.open()">
                      <?= i::__("Publicar Resultados") ?>
                    </button>
                </template>
            </confirm-button>
        </div>
        <div class="col-6">
            <h5><?= i::__("A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.") ?></h5>
        </div>
        <div class="col-3 field">
            <!-- TO DO -->
        </div>
    </div>
</mapas-card>