<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    confirm-button
    mc-stepper-vertical
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="phase-stepper">
            <h2 v-if="index" class="phase-stepper__name">{{item.name}}</h2>
            <h2 v-if="!index" class="phase-stepper__period"><?= i::__('Período de inscrição') ?></h2>
        </div>
    </template>
    <template #default="{index, item}">
        <div v-if="index > 0" class="config-input grid-12" style="padding-bottom: 10px">
            <div class="col-12">
                <p>Status da avaliação: <strong>Em andamento</strong></p>
                <p>Quantidade inscrições: <strong>xxx inscrições</strong></p>
                <p>Quantidade de inscrições avaliadas: <strong>xxx inscrições</strong></p>
                <p>Quantidade de inscrições selecionadas: <strong>XX inscrições</strong></p>
                <p>Quantidade de inscrições suplentes: <strong>XX inscrições</strong></p>
                <p>Quantidade de inscrições inválidas: <strong>XX inscrições</strong></p>
                <p>Quantidade de inscrições pendentes: <strong>XX inscrições</strong></p>
            </div>
            <div class="col-4">
                <button class="button button--primary-outline"><?= i::__("Trazer inscrições da fase anterior") ?></button>
            </div>
            <div class="col-8">
                <h5><?= i::__("Ao trazer as inscrições, você garante que apenas participantes classificados na fase anterior sigam para a póxima fase.") ?></h5>
            </div>
            <div class="phase-delete col-6" >
                <confirm-button message="Confirma a execução da ação?" @confirm="console.log('Lista de Inscricoes da Fase')">
                    <template #button="modal">
                        <a class="button button--text" @click="modal.open()">
                            <label class="phase-delete__label">Lista de inscrições da fase</label>
                            <mc-icon name="external"></mc-icon>
                        </a>
                    </template>
                </confirm-button>
            </div>
            <div class="phase-delete col-6">
                <confirm-button message="Confirma a execução da ação?" @confirm="console.log('Lista de Inscricoes da Fase')">
                    <template #button="modal">
                        <a class="button button--text" @click="modal.open()">
                            <label class="phase-delete__label">Lista de avaliações</label>
                            <mc-icon name="external"></mc-icon>
                        </a>
                    </template>
                </confirm-button>
            </div>
            <div class="config-phase__line-bottom col-12"></div>
            <div class="col-3">
                <button class="button button--primary-outline">Publicar resultado</button>
            </div>
            <div class="col-6">
                <h5>A publicação de um resultado é opcional e só pode ser executada após a aplicação dos resultados das avaliações.</h5>
            </div>
            <div class="col-3 field">
                <label>
                    <input type="checkbox" /> Publicar resultados automaticamente
                </label>
            </div>
        </div>
    </template>
</mc-stepper-vertical>