<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-confirm-button
    mc-loading
    mc-modal
    select-entity
');
?>

<div class="registration-print">
    <div class="registration-print__actions">
        <button class="button button--primary button--icon" @click="print()">
            <mc-icon name="print"></mc-icon> <?= i::__('Imprimir') ?>
        </button>
        
        <button class="button button--primary button--icon" @click="exportPDF()">
            <mc-icon name="download"></mc-icon> <?= i::__('Exportar PDF') ?>
        </button>
        
        <button class="button button--primary button--icon" @click="downloadZip()">
            <mc-icon name="archive"></mc-icon> <?= i::__('Baixar Anexos (ZIP)') ?>
        </button>

        <mc-modal v-if="isSaasSuperAdmin"
                  title="<?= i::__('Trocar agente vinculado') ?>"
                  classes="registration-print__change-agent-modal">
            <template #default="modal">
                <p><?= i::__('Selecione o agente ativo que passará a ser o proponente desta inscrição (incluindo todas as fases com o mesmo número).') ?></p>

                <div v-if="selectedAgent" class="registration-print__selected-agent">
                    <strong>{{ selectedAgent.name }}</strong>
                    <span>(#{{ selectedAgent.id }})</span>
                </div>

                <select-entity type="agent"
                               permissions=""
                               :query="{ status: 'EQ(1)' }"
                               @select="selectAgent($event)">
                    <template #button="{ toggle }">
                        <button class="button button--primary-outline button--icon" @click="toggle()">
                            <mc-icon name="agent"></mc-icon>
                            <?= i::__('Buscar agente') ?>
                        </button>
                    </template>
                </select-entity>

                <mc-alert v-if="errorMessage" type="danger">{{ errorMessage }}</mc-alert>
            </template>

            <template #button="modal">
                <button class="button button--primary button--icon" @click="openChangeAgentModal(modal)">
                    <mc-icon name="exchange"></mc-icon> <?= i::__('Trocar agente vinculado') ?>
                </button>
            </template>

            <template #actions="modal">
                <button class="button button--text" @click="modal.close()" :disabled="changingAgent">
                    <?= i::__('Cancelar') ?>
                </button>
                <mc-confirm-button @confirm="confirmChangeAgent(modal)">
                    <template #button="confirm">
                        <button class="button button--primary"
                                :disabled="!selectedAgent || changingAgent"
                                @click="confirm.open()">
                            <?= i::__('Confirmar troca') ?>
                        </button>
                    </template>
                    <template #message="confirm">
                        <?= i::__('Confirma a troca do agente vinculado a todas as fases desta inscrição?') ?>
                    </template>
                </mc-confirm-button>
            </template>
        </mc-modal>
    </div>

    <iframe ref="printIframe" class="registration-print__printOnly"></iframe>
    <mc-loading class="registration-print__loading" :condition="loading || changingAgent"></mc-loading>
</div>
