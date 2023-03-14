<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    mc-notification
');
?>

<mapas-card>
    <div class="grid-12 opportunity-phase-list-evaluation">
        <div class="col-12">
            <p><?= i::__("Status da avaliação:") ?> <strong>Em andamento</strong></p>
            <p><?= i::__("Quantidade inscrições:") ?> <strong>xxx inscrições</strong></p>
            <p><?= i::__("Quantidade de inscrições avaliadas:") ?> <strong>xxx inscrições</strong></p>
            <p><?= i::__("Quantidade de inscrições selecionadas:") ?> <strong>XX inscrições</strong></p>
            <p><?= i::__("Quantidade de inscrições suplentes:") ?> <strong>XX inscrições</strong></p>
            <p><?= i::__("Quantidade de inscrições inválidas:") ?> <strong>XX inscrições</strong></p>
            <p><?= i::__("Quantidade de inscrições pendentes:") ?> <strong>XX inscrições</strong></p>
        </div>
        <div class="col-4 sm:col-12 subscribe_prev_phase">
            <button class="button button--primary-outline"><?= i::__("Trazer inscrições da fase anterior") ?></button>
        </div>
        <div class="col-8 sm:col-12 subscribe_prev_phase">
            <p><strong><?= i::__("Ao trazer as inscrições, você garante que apenas participantes classificados na fase anterior sigam para a póxima fase.") ?></strong></p>
        </div>
        <div class="col-12">
            <mc-notification type="error">
              <?= i::__("Os resultados das avaliações desta fase já foram aplicados nas inscrições.") ?>
            </mc-notification>
        </div>
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
            <confirm-button message="<?= i::__("Confirma a execução da ação?") ?>" @confirm="console.log('Lista de Inscricoes da Fase')">
                <template #button="modal">
                    <a class="opportunity-phase-list-evaluation_action--button" @click="modal.open()">
                        <label><?= i::__("Lista de inscrições da fase") ?></label>
                        <mc-icon name="external"></mc-icon>
                    </a>
                </template>
            </confirm-button>
        </div>
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
            <confirm-button message="<?= i::__("Confirma a execução da ação?") ?>" @confirm="console.log('Lista de avaliacoes da Fase')">
                <template #button="modal">
                    <a class="opportunity-phase-list-evaluation_action--button" @click="modal.open()">
                        <label class="text--primary"><?= i::__("Lista de avaliações") ?></label>
                        <mc-icon name="external"></mc-icon>
                    </a>
                </template>
            </confirm-button>
        </div>

        <div class="config-phase__line-bottom col-12"></div>
        <div class="col-3">
            <button class="button button--primary-outline"><?= i::__("Publicar resultado") ?></button>
        </div>
        <div class="col-6">
            <h5><?= i::__("A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.") ?></h5>
        </div>
        <div class="col-3 field">
            <label>
                <input type="checkbox" /> <?= i::__("Publicar resultados automaticamente") ?>
            </label>
        </div>
    </div>
</mapas-card>