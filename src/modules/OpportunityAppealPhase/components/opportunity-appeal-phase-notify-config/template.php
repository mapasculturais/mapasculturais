<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-alert
    mc-icon
    mc-loading
    mc-modal
');
?>

<div class="opportunity-appeal-phase-notify-config">
    <?php // Alerta de onboarding: exibido apenas quando nenhuma notificação está ativa. ?>
    <mc-alert v-if="activeFlowCount === 0" type="info" class="opportunity-appeal-phase-notify-config__onboarding">
        <strong><?= i::__('Nenhuma notificação ativa') ?>.</strong>
        <?= i::__('Os participantes não receberão avisos por e-mail sobre recursos. Ative pelo menos uma notificação para que os envios passem a ocorrer.') ?>
    </mc-alert>

    <?php // Grid de 5 cards (2 colunas em telas >= 768px, 1 coluna em mobile). ?>
    <div class="opportunity-appeal-phase-notify-config__grid">
        <div
            v-for="flow in flows"
            :key="flow.id"
            class="opportunity-appeal-phase-notify-config__card"
            :class="{
                'opportunity-appeal-phase-notify-config__card--off': !isFlowOn(flow),
                'opportunity-appeal-phase-notify-config__card--custom': hasCustomText(flow.id),
            }"
        >
            <div class="opportunity-appeal-phase-notify-config__card-header">
                <h4 class="opportunity-appeal-phase-notify-config__card-title bold">
                    {{ flow.label }}
                </h4>

                <?php // Selo "Personalizado" — visível quando subject/message custom existem, mesmo com toggle OFF. ?>
                <span
                    v-if="hasCustomText(flow.id)"
                    class="opportunity-appeal-phase-notify-config__custom-badge"
                    role="status"
                >
                    <?= i::__('Personalizado') ?>
                </span>

                <p class="opportunity-appeal-phase-notify-config__card-recipients">
                    <?= i::__('Destinatários:') ?> <span>{{ flow.recipients }}</span>
                </p>
            </div>

            <div class="opportunity-appeal-phase-notify-config__card-controls">
                <?php
                // Toggle via entity-field (autosave 3000ms), espelhando o padrão das
                // linhas 139-140 do opportunity-appeal-phase-config. O slot customiza
                // o label do checkbox — o componente já cuida de for/aria/role.
                ?>
                <entity-field
                    :entity="entity"
                    type="checkbox"
                    :prop="flow.enabledProp"
                    hide-required
                    :autosave="3000"
                    @change="onToggle(flow, $event)"
                >
                    {{ text('Ativar notificação') }}
                </entity-field>

                <?php // Botão "Personalizar" — sempre clicável, mesmo com toggle OFF. ?>
                <button
                    type="button"
                    class="opportunity-appeal-phase-notify-config__customize-btn button button--text button--md"
                    :aria-label="text('Personalizar') + ' ' + flow.label"
                    @click="openModal(flow.id)"
                >
                    <mc-icon name="edit" size="sm"></mc-icon>
                    <span>{{ text('Personalizar') }}</span>
                </button>
            </div>
        </div>
    </div>

    <?php // Modal de personalização — estado local, Save/Cancel explícitos. ?>
    <mc-modal
        ref="editModal"
        :title="modalTitle"
        :esc-to-close="!isDirty"
        :click-to-close="!isDirty"
        :close-button="!isDirty"
        @close="modalOpen = false"
    >
        <?php // Pilha de estado no canto superior: "Texto padrão" ou "Personalizado". ?>
        <div class="opportunity-appeal-phase-notify-config__modal-pill">
            <span
                v-if="activeFlowIsCustom"
                class="opportunity-appeal-phase-notify-config__pill opportunity-appeal-phase-notify-config__pill--custom"
            >
                <?= i::__('Personalizado') ?>
            </span>
            <span
                v-else
                class="opportunity-appeal-phase-notify-config__pill opportunity-appeal-phase-notify-config__pill--default"
            >
                <?= i::__('Texto padrão') ?>
            </span>
        </div>

        <?php // Campo Assunto — input nativo com v-model sobre o draft. ?>
        <div class="opportunity-appeal-phase-notify-config__field">
            <label class="opportunity-appeal-phase-notify-config__field-label" for="notifySubjectInput">
                <?= i::__('Assunto') ?>
            </label>
            <input
                id="notifySubjectInput"
                ref="subjectInput"
                type="text"
                v-model="draftSubject"
                :maxlength="255"
                class="opportunity-appeal-phase-notify-config__input"
                autocomplete="off"
                @focus="onFocusField('subject')"
            />
        </div>

        <?php // Campo Mensagem — textarea nativo com v-model sobre o draft. ?>
        <div class="opportunity-appeal-phase-notify-config__field">
            <label class="opportunity-appeal-phase-notify-config__field-label" for="notifyMessageInput">
                <?= i::__('Mensagem') ?>
            </label>
            <textarea
                id="notifyMessageInput"
                ref="messageInput"
                v-model="draftMessage"
                rows="6"
                class="opportunity-appeal-phase-notify-config__textarea"
                @focus="onFocusField('message')"
            ></textarea>
        </div>

        <?php // Painel de variáveis — chips clicáveis alimentados por $MAPAS.config. ?>
        <div
            v-if="activeFlowVariables.length > 0"
            class="opportunity-appeal-phase-notify-config__variables"
            role="group"
            :aria-label="text('Variáveis disponíveis')"
        >
            <p class="opportunity-appeal-phase-notify-config__variables-title">
                <?= i::__('Variáveis disponíveis') ?>
            </p>
            <div class="opportunity-appeal-phase-notify-config__variables-list">
                <button
                    v-for="variable in activeFlowVariables"
                    :key="variable.key"
                    type="button"
                    class="opportunity-appeal-phase-notify-config__chip"
                    :aria-label="text('Inserir variável') + ': ' + variable.label"
                    :title="variable.label"
                    @click="insertVariable(variable.key)"
                    v-text="'{{' + variable.key + '}}'"
                ></button>
            </div>
            <p class="opportunity-appeal-phase-notify-config__variables-hint">
                <?= i::__('Clique em uma variável para inseri-la no campo focado') ?>
            </p>
        </div>

        <?php // Indicador de estado no rodapé: "Ativo" / "Inativo" (lê direto da entidade). ?>
        <div class="opportunity-appeal-phase-notify-config__modal-status" role="status" :aria-label="text('Estado atual')">
            <span
                v-if="activeFlowEnabled"
                class="opportunity-appeal-phase-notify-config__pill opportunity-appeal-phase-notify-config__pill--active"
            >
                <?= i::__('Ativo') ?>
            </span>
            <span
                v-else
                class="opportunity-appeal-phase-notify-config__pill opportunity-appeal-phase-notify-config__pill--inactive"
            >
                <?= i::__('Inativo') ?>
            </span>
        </div>

        <?php // Ações do modal: Restaurar padrão + Cancelar + Salvar. ?>
        <template #actions="modal">
            <button
                type="button"
                class="button button--text"
                @click="restoreDefault()"
            >
                <?= i::__('Restaurar padrão') ?>
            </button>

            <button
                type="button"
                class="button button--text"
                @click="cancelModal()"
            >
                <?= i::__('Cancelar') ?>
            </button>

            <button
                type="button"
                class="button button--primary"
                :disabled="!isDirty || processing"
                @click="saveModal()"
            >
                <mc-loading v-if="processing" :condition="processing"></mc-loading>
                <span><?= i::__('Salvar') ?></span>
            </button>
        </template>
    </mc-modal>
</div>
