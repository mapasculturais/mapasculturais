<?php
/**
 * template.php — seal-validator-config
 *
 * Renderiza a configuração de selos validadores por fase de avaliação.
 * Reutiliza: mc-multiselect, mc-tag-list, mc-toggle, mc-alert, mc-icon.
 *
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-icon
    mc-multiselect
    mc-tag-list
    mc-toggle
');
?>

<div class="seal-validator-config">

    <?php $this->applyComponentHook('header', 'before') ?>

    <header class="seal-validator-config__header">
        <p class="seal-validator-config__description">
            <?= i::__('Quando o proponente possuir todos os selos abaixo plenamente válidos no momento de entrada da inscrição nesta fase, a inscrição é dispensada automaticamente (status 10) e avança para a próxima fase, sem passar por avaliação manual.') ?>
        </p>
    </header>

    <?php $this->applyComponentHook('header', 'after') ?>

    <!-- Bloqueio read-only quando a fase aberta já possui inscrições -->
    <mc-alert v-if="!canEdit" type="warning">
        <?= i::__('A fase está aberta e já possui inscrições. A configuração de selos validadores não pode mais ser alterada.') ?>
    </mc-alert>

    <!-- Sem nenhum selo disponível (sem permissão) -->
    <mc-alert v-if="canEdit && !hasAvailableSeals" type="warning">
        <?= i::__('Você não tem permissão para usar nenhum selo. A isenção automática não pode ser configurada.') ?>
    </mc-alert>

    <!-- Toggle habilitar/desabilitar (UI: expande/recolhe a configuração) -->
    <div class="seal-validator-config__toggle field" v-if="hasAvailableSeals || !canEdit">
        <mc-toggle
            :modelValue="expanded"
            @update:modelValue="onToggleExpand"
            label="<?= i::esc_attr__('Habilitar avaliação automática por selos nesta fase') ?>"
        ></mc-toggle>

        <!-- Status real derivado de seals.length > 0 -->
        <span class="seal-validator-config__status" :class="{ 'is-active': isEnabled }">
            <template v-if="isEnabled">
                <mc-icon name="circle-checked"></mc-icon>
                <?= i::__('Isenção ativa') ?> ({{ selectedCount }})
            </template>
            <template v-else>
                <mc-icon name="exclamation"></mc-icon>
                <?= i::__('Isenção inativa') ?>
            </template>
        </span>
    </div>

    <!-- Área de configuração (expandida OU bloqueada em modo leitura) -->
    <div class="seal-validator-config__body" v-if="expanded || !canEdit">

        <!-- Estado vazio: nenhum selo configurado -->
        <mc-alert v-if="!isEnabled" type="helper">
            <?= i::__('Nenhum selo validador configurado. A isenção automática está desativada nesta fase.') ?>
        </mc-alert>

        <!-- Help text: critério de validade (fully_valid) -->
        <p class="seal-validator-config__validity-help">
            <mc-icon name="info-full"></mc-icon>
            <?= i::__('O proponente só será isento se TODOS os selos estiverem totalmente válidos (fully_valid). Selos parcialmente válidos ou pendentes NÃO contam.') ?>
        </p>

        <!-- Seleção de selos -->
        <div class="seal-validator-config__field field">
            <label class="field__title semibold"><?= i::_e('Selos validadores') ?></label>

            <mc-multiselect
                v-if="hasAvailableSeals"
                :items="availableSeals"
                :model="config.seals"
                :disabled="!canEdit"
                :hide-button="true"
                placeholder="<?= i::esc_attr__('Busque e selecione os selos validadores') ?>"
                @selected="onSealSelected"
                @removed="onSealRemoved"
            ></mc-multiselect>

            <!-- Contador de transparência: selos sem permissão (ocultos) -->
            <p class="seal-validator-config__denied-footer" v-if="deniedSealsCount > 0">
                {{ deniedSealsCount }} <?= i::__('selo(s) não disponível(is) por falta de permissão') ?>
            </p>

            <!-- Selos selecionados (tags com nome) -->
            <mc-tag-list
                v-if="selectedCount > 0"
                :tags="config.seals"
                :labels="sealLabels"
                :editable="canEdit"
                @remove="onSealRemoved"
            ></mc-tag-list>

            <!-- Selos configurados que não estão mais disponíveis (inativos/removidos) -->
            <ul class="seal-validator-config__inactive" v-if="inactiveSelectedSeals.length > 0">
                <li v-for="id in inactiveSelectedSeals" :key="id" class="seal-validator-config__inactive-tag">
                    <mc-icon name="exclamation" class="warning__color"></mc-icon>
                    <?= i::__('Selo ID') ?> {{ id }} — <?= i::__('inativo') ?>
                </li>
            </ul>

            <!-- Remover todos (desativar) -->
            <button
                v-if="canEdit && isEnabled"
                class="button button--text button--sm button--text-danger"
                @click="removeAllSeals"
            >
                <mc-icon name="trash" class="danger__color"></mc-icon>
                <?= i::_e('Remover todos os selos') ?>
            </button>
        </div>

        <!-- Rótulo configurável -->
        <div class="seal-validator-config__field field">
            <label class="field__title semibold" for="seal-validator-label">
                <?= i::_e('Rótulo exibido da isenção') ?>
            </label>
            <input
                id="seal-validator-label"
                type="text"
                class="seal-validator-config__label-input"
                maxlength="80"
                v-model="config.label"
                @change="onLabelChange"
                :disabled="!canEdit"
                placeholder="<?= i::esc_attr__('Isento por selos válidos') ?>"
            >
            <span class="seal-validator-config__label-help">
                <?= i::__('Texto exibido na tabela de avaliação, na planilha e no acompanhamento do proponente. Se vazio, será usado o rótulo padrão.') ?>
            </span>
        </div>

    </div>

    <?php $this->applyComponentHook('bottom') ?>
</div>
