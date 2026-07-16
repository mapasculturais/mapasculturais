<?php
/**
 * template.php — seal-validator-config
 *
 * Renderiza a configuração de selos validadores por fase de avaliação.
 * Reutiliza: mc-multiselect, mc-toggle, mc-alert, mc-icon.
 *
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-icon
    mc-multiselect
    mc-select
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

    <!-- Bloqueio read-only quando a fase aberta já possui inscrições enviadas -->
    <mc-alert v-if="!canEdit" type="warning">
        <?= i::__('A fase está aberta e já possui inscrições enviadas. A configuração de avaliação automática por selos não pode mais ser alterada nem desativada.') ?>
    </mc-alert>

    <!-- Sem nenhum selo disponível (sem permissão) -->
    <mc-alert v-if="canEdit && !hasAvailableSeals" type="warning">
        <?= i::__('Você não tem permissão para usar nenhum selo. A isenção automática não pode ser configurada.') ?>
    </mc-alert>

    <!-- Toggle habilitar/desabilitar (UI: expande/recolhe a configuração) -->
    <div class="seal-validator-config__toggle field" v-if="hasAvailableSeals || !canEdit">
        <mc-toggle
            :modelValue="isEnabled"
            @update:modelValue="onToggleEnabled"
            :disabled="!canEdit"
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

            <!-- Selos selecionados (tags; amarelo = pendências de invalidadores) -->
            <div v-if="selectedCount > 0" class="seal-validator-config__selected">
                <p class="seal-validator-config__selected-label field__title">
                    <?= i::_e('Selos selecionados') ?>
                </p>
                <ul class="seal-validator-config__tag-list">
                    <li
                        v-for="seal in selectedSealsWithStatus"
                        :key="seal.id"
                        class="seal-validator-config__tag"
                        :class="{
                            'seal-validator-config__tag--pending': seal.hasPending,
                            'seal-validator-config__tag--editable': canEdit,
                        }"
                    >
                        <mc-icon v-if="seal.hasPending" name="exclamation"></mc-icon>
                        <span>{{ seal.label }}</span>
                        <mc-icon
                            v-if="canEdit"
                            name="delete"
                            is-link
                            @click="removeSelectedSeal(seal.id)"
                        ></mc-icon>
                    </li>
                </ul>
            </div>

            <!-- Condicionalidade de invalidadores (spec-fe9b2cfc) -->
            <details v-if="canEdit && isEnabled" class="seal-validator-config__conditions">
                <summary class="seal-validator-config__conditions-title field__title">
                    <?= i::_e('Condicionalidade de invalidadores') ?>
                </summary>

                <div class="seal-validator-config__conditions-body">
                    <p class="seal-validator-config__conditions-intro">
                        <?= i::__('Faça com que um campo invalidador só seja exigido quando o proponente preencher determinado campo do formulário. Quando a condição não se aplica, o invalidador é relevado.') ?>
                    </p>

                    <div
                        v-for="seal in selectedSealsWithStatus"
                        :key="'cond-' + seal.id"
                        class="seal-validator-config__condition-seal"
                    >
                        <p class="seal-validator-config__condition-seal-name">
                            <strong>{{ seal.label }}</strong>
                        </p>

                        <div
                            v-for="inv in invalidatorsBySeal(seal.id)"
                            :key="'inv-' + seal.id + '-' + inv.fieldKey"
                            class="seal-validator-config__condition-item"
                        >
                            <div class="seal-validator-config__condition-header">
                                <div class="seal-validator-config__condition-title-wrap">
                                    <span class="seal-validator-config__condition-field-label">{{ inv.label }}</span>
                                    <span
                                        v-if="conditionsForSeal(seal.id)[inv.fieldKey]"
                                        class="seal-validator-config__condition-badge"
                                    >
                                        <?= i::_e('Condicionado') ?>
                                    </span>
                                </div>
                                <button
                                    v-if="!conditionsForSeal(seal.id)[inv.fieldKey]"
                                    type="button"
                                    class="button button--primary-outline button--sm"
                                    :disabled="!canEdit"
                                    @click="addCondition(seal.id, inv.fieldKey)"
                                >
                                    + <?= i::_e('Condicionar') ?>
                                </button>
                            </div>

                            <div v-if="conditionsForSeal(seal.id)[inv.fieldKey]" class="seal-validator-config__condition-config">
                                <div
                                    v-for="(clause, idx) in conditionsForSeal(seal.id)[inv.fieldKey].clauses"
                                    :key="'clause-' + idx"
                                    class="seal-validator-config__clause"
                                >
                                    <div class="seal-validator-config__clause-step">
                                        <span class="seal-validator-config__clause-step-label">
                                            {{ text('conditionFieldLabel') }}
                                        </span>
                                        <mc-select
                                            :default-value="clause.field"
                                            @change-option="onClauseFieldChange(seal.id, inv.fieldKey, idx, $event)"
                                            :options="conditionalFields.map(f => ({ value: f.fieldName, label: f.title }))"
                                            :placeholder="text('conditionFieldPlaceholder')"
                                            :disabled="!canEdit"
                                        ></mc-select>
                                    </div>

                                    <div v-if="clause.field" class="seal-validator-config__clause-step">
                                        <span class="seal-validator-config__clause-step-label">
                                            {{ text('conditionValuesLabel') }}
                                        </span>
                                        <div
                                            class="seal-validator-config__clause-chips"
                                            :class="{
                                                'seal-validator-config__clause-chips--single': conditionalFields.find(f => f.fieldName === clause.field)?.fieldType === 'checkbox'
                                            }"
                                        >
                                            <button
                                                v-for="opt in getFieldOptions(conditionalFields.find(f => f.fieldName === clause.field))"
                                                :key="opt.value"
                                                type="button"
                                                class="seal-validator-config__chip"
                                                :class="{ 'seal-validator-config__chip--selected': clause.values.includes(opt.value) }"
                                                :disabled="!canEdit"
                                                @click="conditionalFields.find(f => f.fieldName === clause.field)?.fieldType === 'checkbox'
                                                    ? setConditionSingleValue(seal.id, inv.fieldKey, idx, opt.value)
                                                    : toggleConditionValue(seal.id, inv.fieldKey, idx, opt.value)"
                                            >
                                                {{ opt.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="clause.field && clause.values.length > 0" class="seal-validator-config__clause-preview">
                                        <mc-icon name="circle-checked"></mc-icon>
                                        <span>{{ text('conditionPreviewTemplate') }} <strong>{{ clausePreview(clause) }}</strong></span>
                                    </div>

                                    <div class="seal-validator-config__clause-actions">
                                        <button
                                            type="button"
                                            class="button button--text button--sm button--text-danger"
                                            :disabled="!canEdit"
                                            @click="removeClause(seal.id, inv.fieldKey, idx)"
                                        >
                                            {{ text('conditionRemoveClause') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="seal-validator-config__condition-actions">
                                    <button
                                        type="button"
                                        class="button button--primary-outline button--sm"
                                        :disabled="!canEdit"
                                        @click="addCondition(seal.id, inv.fieldKey)"
                                    >
                                        + {{ text('conditionAddClause') }}
                                    </button>
                                    <button
                                        type="button"
                                        class="button button--text button--sm button--text-danger"
                                        :disabled="!canEdit"
                                        @click="removeCondition(seal.id, inv.fieldKey)"
                                    >
                                        {{ text('conditionRemove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <mc-alert type="warning">
                        {{ text('conditionBlankAlert') }}
                    </mc-alert>
                </div>
            </details>

            <!-- Pendências: invalidadores do selo ausentes no formulário -->
            <div v-if="hasPendingInvalidators" class="seal-validator-config__pending">
                <mc-alert type="warning">
                    <strong>{{ text('pendingTitle') }}</strong>
                    — {{ text('pendingIntro') }}
                </mc-alert>
                <ul class="seal-validator-config__pending-list">
                    <li v-for="seal in pendingSeals" :key="'pending-' + seal.id">
                        <strong>{{ text('pendingSealPrefix') }}: {{ seal.label }}</strong>
                        <span class="seal-validator-config__pending-fields-label">{{ text('pendingFieldsLabel') }}:</span>
                        <ul>
                            <li v-for="field in seal.missingInvalidators" :key="field.fieldKey">
                                {{ field.label }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

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

    </div>

    <?php $this->applyComponentHook('bottom') ?>
</div>
