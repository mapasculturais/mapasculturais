<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-confirm-button
    mc-datepicker
    mc-icon
    mc-loading
    mc-modal
');
?>

<mc-modal ref="modal" classes="opportunity-appeal-correction-assignment" :title="text('title')" :subtitle="subtitle">
    <template #default="{ close }">
        <div class="opportunity-appeal-correction-assignment__content">
            <mc-alert v-if="!endpointAvailable" type="warning">{{ text('endpoint unavailable') }}</mc-alert>

            <mc-alert v-if="!correctionEligible" type="warning">{{ text('eligible context required') }}</mc-alert>

            <mc-loading :condition="loading">{{ text('loading') }}</mc-loading>

            <div v-if="!loading && !slots.length" class="opportunity-appeal-correction-assignment__empty">
                {{ text('empty slots') }}
            </div>

            <div v-if="!loading && slots.length" class="opportunity-appeal-correction-assignment__progress semibold">
                <mc-icon name="circle" class="primary__color"></mc-icon>
                {{ progressSummary }}
            </div>

            <div v-if="!loading && slots.length" class="opportunity-appeal-correction-assignment__header">
                <div><?= i::__('Avaliação') ?></div>
                <div><?= i::__('Corretor designado') ?></div>
                <div><?= i::__('Acompanhamento') ?></div>
            </div>

            <div
                v-for="slot in slots"
                :key="slot.id"
                class="opportunity-appeal-correction-assignment__slot"
                :class="{
                    'opportunity-appeal-correction-assignment__slot--disabled': isSlotLocked(slot),
                    'opportunity-appeal-correction-assignment__slot--assigned': !!reviewForSlot(slot),
                }">

                <div class="opportunity-appeal-correction-assignment__slot-score">
                    <!-- Slot designado: leitura estática do dono (sem controle desabilitado) -->
                    <div v-if="reviewForSlot(slot)" class="opportunity-appeal-correction-assignment__slot-owner semibold">
                        <mc-icon name="circle" :class="statusClass(reviewForSlot(slot))"></mc-icon>
                        <span>{{ slotAgentName(slot) }}</span>
                    </div>

                    <label
                        v-else
                        class="opportunity-appeal-correction-assignment__slot-check"
                        :class="{ 'semibold': slot.checked }"
                        :for="'correct-slot-' + slot.id">

                        <input
                            type="checkbox"
                            :id="'correct-slot-' + slot.id"
                            :name="'correct-slot-' + slot.id"
                            v-model="slot.checked"
                            :disabled="!slotSelectable(slot)">

                        <span>
                            <span class="semibold">{{ text('correct this score') }}</span>
                            <span class="opportunity-appeal-correction-assignment__slot-valuer">
                                {{ slotAgentName(slot) }}
                            </span>
                        </span>
                    </label>

                    <div class="opportunity-appeal-correction-assignment__slot-result">
                        {{ slot.resultString }}
                    </div>

                    <span
                        v-if="activeReviewForSlot(slot)"
                        class="opportunity-appeal-correction-assignment__tag warning__background">
                        {{ text('already designated') }}
                    </span>
                </div>

                <div class="opportunity-appeal-correction-assignment__slot-corrector">
                    <!-- Slot designado: corretor como leitura estática com papel -->
                    <div v-if="reviewForSlot(slot)" class="opportunity-appeal-correction-assignment__assigned">
                        <mc-icon name="agent" class="primary__color"></mc-icon>
                        <span class="semibold">{{ reviewCorrectorName(reviewForSlot(slot)) || '—' }}</span>
                        <span class="opportunity-appeal-correction-assignment__tag opportunity-appeal-correction-assignment__tag--role">
                            {{ correctorRoleLabel(slot, reviewForSlot(slot)) }}
                        </span>
                    </div>

                    <template v-else>
                        <select
                            class="opportunity-appeal-correction-assignment__select"
                            :id="'corrector-select-' + slot.id"
                            :name="'corrector-select-' + slot.id"
                            :aria-label="text('select corrector') + ' — ' + slotAgentName(slot)"
                            v-model="slot.correctorUserId"
                            :disabled="!slot.checked || !slotSelectable(slot)">

                            <option :value="null" disabled>{{ text('select corrector') }}</option>
                            <option
                                v-for="option in correctorOptions(slot)"
                                :key="option.value"
                                :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>

                        <div
                            v-if="slot.checked && !slot.correctorUserId"
                            class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                            <?= i::__('Selecione o corretor para salvar esta designação') ?>
                        </div>

                        <div v-if="slot.checked" class="opportunity-appeal-correction-assignment__deadline">
                            <label class="semibold" :for="'deadline-' + slot.id"><?= i::__('Prazo final da correção (opcional)') ?></label>
                            <mc-datepicker
                                :key="'deadline-' + slot.id + '-' + slot.endsAtKey"
                                :prop-id="'deadline-' + slot.id"
                                field-type="datetime"
                                @update:model-value="setSlotDeadline(slot, $event)">
                            </mc-datepicker>

                            <div v-if="slotDeadlineError(slot)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                {{ slotDeadlineError(slot) }}
                            </div>
                            <div v-else-if="slot.endsAt" class="opportunity-appeal-correction-assignment__slot-hint">
                                {{ text('deadline until') }} {{ formatDate(slot.endsAt.toISOString()) }}
                                <button type="button" class="button button--sm button--text primary__color" @click="clearSlotDeadline(slot)">
                                    <?= i::__('Remover prazo') ?>
                                </button>
                            </div>
                            <div v-else class="opportunity-appeal-correction-assignment__slot-hint">
                                {{ text('no deadline hint') }}
                            </div>
                        </div>

                        <!-- F6 (#49): seletor de escopo (só para métodos com critérios) -->
                        <div v-if="slot.checked && hasCriteriaSelector" class="opportunity-appeal-correction-assignment__scope">
                            <button
                                type="button"
                                class="button button--sm button--text primary__color"
                                @click="slot.scopeOpen = !slot.scopeOpen">

                                <mc-icon :name="slot.scopeOpen ? 'arrow-up' : 'arrow-down'"></mc-icon>
                                {{ text('criteria scope') }} ({{ slotScopeCount(slot) }}/{{ criteriaCatalog.total }})
                            </button>

                            <div v-if="slot.scopeOpen" class="opportunity-appeal-correction-assignment__scope-list">
                                <div
                                    v-for="section in criteriaCatalog.sections"
                                    :key="section.id || section.name"
                                    class="opportunity-appeal-correction-assignment__scope-section">

                                    <div v-if="section.name" class="semibold">{{ section.name }}</div>

                                    <label
                                        v-for="item in section.items"
                                        :key="item.id"
                                        class="opportunity-appeal-correction-assignment__scope-item"
                                        :for="'scope-' + slot.id + '-' + item.id">

                                        <input
                                            type="checkbox"
                                            :id="'scope-' + slot.id + '-' + item.id"
                                            :name="'scope-' + slot.id + '[]'"
                                            :value="item.id"
                                            v-model="slot.scopeCriteria">

                                        <span>{{ item.label }}</span>
                                    </label>
                                </div>

                                <div v-if="scopeInvalidFrom(slot.scopeCriteria)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                    {{ text('select at least one criterion') }}
                                </div>
                            </div>
                        </div>

                        <!-- Campos do formulário: espelha fields-visible-evaluators -->
                        <div v-if="slot.checked && hasFormFieldsSelector" class="opportunity-appeal-correction-assignment__scope">
                            <mc-modal
                                classes="modalEmbedTools opportunity-appeal-correction-assignment__fields-modal"
                                :title="text('form fields modal title')">
                                <template #default>
                                    <div class="fields-visible-evaluators opportunity-appeal-correction-assignment__fields-picker">
                                        <p class="opportunity-appeal-correction-assignment__slot-hint">
                                            {{ text('form fields modal subtitle') }}
                                        </p>

                                        <div class="fields-visible-evaluators__search-wrapper field--horizontal">
                                            <label class="semibold">{{ text('form fields filter') }}</label>
                                            <div class="fields-visible-evaluators__search field">
                                                <input type="text" v-model="formFieldsSearchQuery">
                                                <small>{{ text('form fields search hint') }}</small>
                                            </div>
                                        </div>

                                        <div class="fields-visible-evaluators__content">
                                            <div class="fields-visible-evaluators__fields field">
                                                <div class="fields-visible-evaluators__field__select-all field">
                                                    <input
                                                        type="checkbox"
                                                        :id="'form-fields-all-' + slot.id"
                                                        :checked="slotFormFieldsCount(slot) === formFieldsCatalog.total"
                                                        @change="toggleSelectAllFormFields(slot)">
                                                    <label :for="'form-fields-all-' + slot.id">{{ text('select all form fields') }}</label>
                                                </div>

                                                <div
                                                    v-for="item in filteredFormFields"
                                                    :key="item.id"
                                                    class="fields-visible-evaluators__field field">
                                                    <label :for="'form-field-' + slot.id + '-' + item.id">
                                                        <mc-icon :name="formFieldIcon(item)"></mc-icon>
                                                        <input
                                                            type="checkbox"
                                                            :id="'form-field-' + slot.id + '-' + item.id"
                                                            :value="item.id"
                                                            v-model="slot.formFields">
                                                        <span v-if="item.numericId">#{{ item.numericId }}</span>
                                                        <div class="fields-visible-evaluators__field__title">
                                                            <span>{{ item.label }}</span>
                                                            <small>{{ item.id }}</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template #button="modal">
                                    <button
                                        type="button"
                                        class="button button--bg button--primarylight"
                                        @click="openFormFieldsModal(modal)">
                                        {{ text('open form fields list') }}
                                        ({{ slotFormFieldsCount(slot) }}/{{ formFieldsCatalog.total }})
                                    </button>
                                </template>
                            </mc-modal>

                            <div v-if="formFieldsInvalidFrom(slot.formFields)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                {{ text('select at least one form field') }}
                            </div>
                        </div>

                        <!-- CA-4 / CA-6: o que o corretor pode ver no ambiente -->
                        <div v-if="slot.checked" class="opportunity-appeal-correction-assignment__visibility">
                            <label class="opportunity-appeal-correction-assignment__visibility-item" :for="'show-opinion-' + slot.id">
                                <input
                                    type="checkbox"
                                    :id="'show-opinion-' + slot.id"
                                    v-model="slot.showAppealOpinion">
                                <span>{{ text('show appeal opinion') }}</span>
                            </label>
                            <label class="opportunity-appeal-correction-assignment__visibility-item" :for="'show-valuer-' + slot.id">
                                <input
                                    type="checkbox"
                                    :id="'show-valuer-' + slot.id"
                                    v-model="slot.showOriginalValuerName">
                                <span>{{ text('show original valuer name') }}</span>
                            </label>
                        </div>
                    </template>
                </div>

                <div class="opportunity-appeal-correction-assignment__slot-status">
                    <template v-if="reviewForSlot(slot)">
                        <div
                            class="opportunity-appeal-correction-assignment__status semibold"
                            :class="statusClass(reviewForSlot(slot))">

                            <mc-icon name="circle" :class="statusClass(reviewForSlot(slot))"></mc-icon>
                            {{ statusLabel(reviewForSlot(slot)) }}
                        </div>

                        <div v-if="reviewCorrectorName(reviewForSlot(slot))" class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="agent"></mc-icon>
                            <span><?= i::__('Corretor') ?>: {{ reviewCorrectorName(reviewForSlot(slot)) }}</span>
                        </div>

                        <div v-if="reviewDeadline(reviewForSlot(slot))" class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="clock"></mc-icon>
                            <span><?= i::__('Prazo') ?>: {{ reviewDeadline(reviewForSlot(slot)) }}</span>
                        </div>

                        <div v-if="reviewSentAt(reviewForSlot(slot))" class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="send"></mc-icon>
                            <span>{{ reviewSentAtLabel(reviewForSlot(slot)) }}: {{ reviewSentAt(reviewForSlot(slot)) }}</span>
                        </div>

                        <div class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="eye"></mc-icon>
                            <span>{{ reviewShowsAppealOpinion(reviewForSlot(slot)) ? text('appeal opinion visible') : text('appeal opinion hidden') }}</span>
                        </div>

                        <div class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="agent"></mc-icon>
                            <span>{{ reviewShowsOriginalValuerName(reviewForSlot(slot)) ? text('original valuer visible') : text('original valuer hidden') }}</span>
                        </div>

                        <!-- F6: escopo da designação (todos liberados / X de Y) -->
                        <div class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="list"></mc-icon>
                            <span>{{ reviewScopeLabel(reviewForSlot(slot)) }}</span>
                        </div>

                        <div v-if="hasFormFieldsSelector" class="opportunity-appeal-correction-assignment__status-detail">
                            <mc-icon name="list"></mc-icon>
                            <span>{{ reviewFormFieldsLabel(reviewForSlot(slot)) }}</span>
                        </div>

                        <!-- F5 (#45): gestão de designação ativa (substituir/cancelar) — únicos controles ativos da linha -->
                        <div v-if="canManageReview(reviewForSlot(slot))" class="opportunity-appeal-correction-assignment__actions">
                            <button
                                class="button button--sm button--text primary__color"
                                :class="{ 'disabled': slot.substituting || slot.canceling }"
                                :disabled="slot.substituting || slot.canceling"
                                @click="startSubstitution(slot)">

                                <?= i::__('Substituir corretor') ?>
                            </button>

                            <button
                                class="button button--sm button--text primary__color"
                                :class="{ 'disabled': slot.substituting || slot.canceling || slot.changingDeadline }"
                                :disabled="slot.substituting || slot.canceling || slot.changingDeadline"
                                @click="startDeadlineEdit(slot)">

                                <?= i::__('Alterar prazo') ?>
                            </button>

                            <mc-confirm-button v-if="canCancelReview(reviewForSlot(slot))" :loading="slot.canceling || false" @confirm="cancelAssignment(slot)">
                                <template #button="modal">
                                    <button
                                        class="button button--sm button--text-danger"
                                        :class="{ 'disabled': slot.substituting || slot.canceling }"
                                        :disabled="slot.substituting || slot.canceling"
                                        @click="modal.open()">

                                        <?= i::__('Cancelar designação') ?>
                                    </button>
                                </template>
                                <template #message>
                                    <?= i::__('Tem certeza que deseja cancelar esta designação? Esta avaliação voltará a ficar disponível para nova designação.') ?>
                                </template>
                            </mc-confirm-button>
                        </div>

                        <div v-if="canReopenReview(reviewForSlot(slot)) && !slot.reopenOpen" class="opportunity-appeal-correction-assignment__actions">
                            <button
                                class="button button--sm button--text primary__color"
                                @click="startReopen(slot)">

                                <mc-icon name="edit"></mc-icon>
                                <?= i::__('Reabrir correção') ?>
                            </button>
                        </div>

                        <!-- Alteração de prazo inline (designação ativa) -->
                        <div v-if="canManageReview(reviewForSlot(slot)) && slot.deadlineOpen" class="opportunity-appeal-correction-assignment__substitution">
                            <div class="opportunity-appeal-correction-assignment__slot-hint semibold">
                                <template v-if="reviewDeadline(reviewForSlot(slot))">
                                    {{ reviewDeadlineExpired(reviewForSlot(slot)) ? text('current deadline expired') : text('current deadline') }}
                                    {{ reviewDeadline(reviewForSlot(slot)) }}
                                </template>
                                <template v-else>{{ text('current no deadline') }}</template>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__deadline">
                                <label class="semibold" :for="'edit-deadline-' + slot.id"><?= i::__('Novo prazo final') ?></label>
                                <mc-datepicker
                                    v-if="!slot.deadlineRemove"
                                    :key="'edit-deadline-' + slot.id + '-' + slot.deadlineEndsAtKey"
                                    :prop-id="'edit-deadline-' + slot.id"
                                    field-type="datetime"
                                    @update:model-value="setSlotDeadline(slot, $event, 'deadlineEndsAt')">
                                </mc-datepicker>

                                <label
                                    v-if="reviewDeadline(reviewForSlot(slot))"
                                    class="opportunity-appeal-correction-assignment__scope-item"
                                    :for="'remove-deadline-' + slot.id">
                                    <input
                                        type="checkbox"
                                        :id="'remove-deadline-' + slot.id"
                                        v-model="slot.deadlineRemove"
                                        @change="clearSlotDeadline(slot, 'deadlineEndsAt')">
                                    <span><?= i::__('Remover prazo (corretor pode corrigir a qualquer momento)') ?></span>
                                </label>

                                <div v-if="slotDeadlineError(slot, 'deadlineEndsAt')" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                    {{ slotDeadlineError(slot, 'deadlineEndsAt') }}
                                </div>
                                <div v-else-if="slot.deadlineEndsAt" class="opportunity-appeal-correction-assignment__slot-hint">
                                    {{ text('deadline until') }} {{ formatDate(slot.deadlineEndsAt.toISOString()) }}
                                </div>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__substitution-actions">
                                <button
                                    class="button button--sm button--primary"
                                    :class="{ 'disabled': !canConfirmDeadline(slot) }"
                                    :disabled="!canConfirmDeadline(slot)"
                                    @click="confirmDeadline(slot)">

                                    {{ slot.changingDeadline ? text('changing deadline') : text('confirm deadline') }}
                                </button>

                                <button
                                    class="button button--sm button--text"
                                    :class="{ 'disabled': slot.changingDeadline }"
                                    :disabled="slot.changingDeadline"
                                    @click="cancelDeadlineEdit(slot)">

                                    <?= i::__('Cancelar') ?>
                                </button>
                            </div>
                        </div>

                        <!-- Reabertura inline: prazo atual, novo prazo ou sem prazo -->
                        <div v-if="canReopenReview(reviewForSlot(slot)) && slot.reopenOpen" class="opportunity-appeal-correction-assignment__substitution">
                            <div class="opportunity-appeal-correction-assignment__slot-hint">
                                <?= i::__('O mesmo corretor poderá revisar e enviar novamente. A nota corrigida atual continua valendo até o novo envio. As demais avaliações não são afetadas.') ?>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__slot-hint semibold">
                                <template v-if="reviewDeadline(reviewForSlot(slot))">
                                    {{ reviewDeadlineExpired(reviewForSlot(slot)) ? text('current deadline expired') : text('current deadline') }}
                                    {{ reviewDeadline(reviewForSlot(slot)) }}
                                </template>
                                <template v-else>{{ text('current no deadline') }}</template>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__deadline">
                                <label class="semibold" :for="'reopen-deadline-' + slot.id"><?= i::__('Novo prazo final (opcional)') ?></label>
                                <mc-datepicker
                                    v-if="!slot.reopenWithoutDeadline"
                                    :key="'reopen-deadline-' + slot.id + '-' + slot.reopenEndsAtKey"
                                    :prop-id="'reopen-deadline-' + slot.id"
                                    field-type="datetime"
                                    @update:model-value="setSlotDeadline(slot, $event, 'reopenEndsAt')">
                                </mc-datepicker>

                                <label class="opportunity-appeal-correction-assignment__scope-item" :for="'reopen-no-deadline-' + slot.id">
                                    <input
                                        type="checkbox"
                                        :id="'reopen-no-deadline-' + slot.id"
                                        v-model="slot.reopenWithoutDeadline"
                                        @change="clearSlotDeadline(slot, 'reopenEndsAt')">
                                    <span><?= i::__('Reabrir sem prazo') ?></span>
                                </label>

                                <div v-if="reopenDeadlineError(slot)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                    {{ reopenDeadlineError(slot) }}
                                </div>
                                <div v-else-if="slot.reopenEndsAt" class="opportunity-appeal-correction-assignment__slot-hint">
                                    {{ text('deadline until') }} {{ formatDate(slot.reopenEndsAt.toISOString()) }}
                                </div>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__substitution-actions">
                                <button
                                    class="button button--sm button--primary"
                                    :class="{ 'disabled': !canConfirmReopen(slot) }"
                                    :disabled="!canConfirmReopen(slot)"
                                    @click="reopenAssignment(slot)">

                                    {{ slot.reopening ? text('reopening') : text('confirm reopen') }}
                                </button>

                                <button
                                    class="button button--sm button--text"
                                    :class="{ 'disabled': slot.reopening }"
                                    :disabled="slot.reopening"
                                    @click="cancelReopen(slot)">

                                    <?= i::__('Cancelar') ?>
                                </button>
                            </div>
                        </div>

                        <!-- F5: substituição inline -->
                        <div v-if="slot.substitutionOpen" class="opportunity-appeal-correction-assignment__substitution">
                            <select
                                class="opportunity-appeal-correction-assignment__select"
                                :id="'substitute-corrector-' + slot.id"
                                :name="'substitute-corrector-' + slot.id"
                                :aria-label="text('select substitute') + ' — ' + slotAgentName(slot)"
                                v-model="slot.substituteUserId"
                                :disabled="slot.substituting">

                                <option :value="null" disabled>{{ text('select substitute') }}</option>
                                <option
                                    v-for="option in correctorOptions(slot)"
                                    :key="option.value"
                                    :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>

                            <div v-if="slot.substituteUserId == null" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                <?= i::__('Selecione o novo corretor para confirmar') ?>
                            </div>

                            <!-- F6: escopo pré-preenchido com o vigente na substituição -->
                            <div v-if="hasCriteriaSelector" class="opportunity-appeal-correction-assignment__scope">
                                <button
                                    type="button"
                                    class="button button--sm button--text primary__color"
                                    @click="slot.substituteScopeOpen = !slot.substituteScopeOpen">

                                    <mc-icon :name="slot.substituteScopeOpen ? 'arrow-up' : 'arrow-down'"></mc-icon>
                                    {{ text('criteria scope') }} ({{ (slot.substituteScopeCriteria || []).length }}/{{ criteriaCatalog.total }})
                                </button>

                                <div v-if="slot.substituteScopeOpen" class="opportunity-appeal-correction-assignment__scope-list">
                                    <div
                                        v-for="section in criteriaCatalog.sections"
                                        :key="section.id || section.name"
                                        class="opportunity-appeal-correction-assignment__scope-section">

                                        <div v-if="section.name" class="semibold">{{ section.name }}</div>

                                        <label
                                            v-for="item in section.items"
                                            :key="item.id"
                                            class="opportunity-appeal-correction-assignment__scope-item"
                                            :for="'substitute-scope-' + slot.id + '-' + item.id">

                                            <input
                                                type="checkbox"
                                                :id="'substitute-scope-' + slot.id + '-' + item.id"
                                                :name="'substitute-scope-' + slot.id + '[]'"
                                                :value="item.id"
                                                v-model="slot.substituteScopeCriteria">

                                            <span>{{ item.label }}</span>
                                        </label>
                                    </div>

                                    <div v-if="scopeInvalidFrom(slot.substituteScopeCriteria)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                        {{ text('select at least one criterion') }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="hasFormFieldsSelector" class="opportunity-appeal-correction-assignment__scope">
                                <mc-modal
                                    classes="modalEmbedTools opportunity-appeal-correction-assignment__fields-modal"
                                    :title="text('form fields modal title')">
                                    <template #default>
                                        <div class="fields-visible-evaluators opportunity-appeal-correction-assignment__fields-picker">
                                            <p class="opportunity-appeal-correction-assignment__slot-hint">
                                                {{ text('form fields modal subtitle') }}
                                            </p>

                                            <div class="fields-visible-evaluators__search-wrapper field--horizontal">
                                                <label class="semibold">{{ text('form fields filter') }}</label>
                                                <div class="fields-visible-evaluators__search field">
                                                    <input type="text" v-model="formFieldsSearchQuery" :disabled="slot.substituting">
                                                    <small>{{ text('form fields search hint') }}</small>
                                                </div>
                                            </div>

                                            <div class="fields-visible-evaluators__content">
                                                <div class="fields-visible-evaluators__fields field">
                                                    <div class="fields-visible-evaluators__field__select-all field">
                                                        <input
                                                            type="checkbox"
                                                            :id="'sub-form-fields-all-' + slot.id"
                                                            :checked="(slot.substituteFormFields || []).length === formFieldsCatalog.total"
                                                            :disabled="slot.substituting"
                                                            @change="toggleSelectAllFormFields(slot, 'substituteFormFields')">
                                                        <label :for="'sub-form-fields-all-' + slot.id">{{ text('select all form fields') }}</label>
                                                    </div>

                                                    <div
                                                        v-for="item in filteredFormFields"
                                                        :key="item.id"
                                                        class="fields-visible-evaluators__field field">
                                                        <label :for="'sub-form-field-' + slot.id + '-' + item.id">
                                                            <mc-icon :name="formFieldIcon(item)"></mc-icon>
                                                            <input
                                                                type="checkbox"
                                                                :id="'sub-form-field-' + slot.id + '-' + item.id"
                                                                :value="item.id"
                                                                v-model="slot.substituteFormFields"
                                                                :disabled="slot.substituting">
                                                            <span v-if="item.numericId">#{{ item.numericId }}</span>
                                                            <div class="fields-visible-evaluators__field__title">
                                                                <span>{{ item.label }}</span>
                                                                <small>{{ item.id }}</small>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template #button="modal">
                                        <button
                                            type="button"
                                            class="button button--bg button--primarylight"
                                            :disabled="slot.substituting"
                                            @click="openFormFieldsModal(modal)">
                                            {{ text('open form fields list') }}
                                            ({{ (slot.substituteFormFields || []).length }}/{{ formFieldsCatalog.total }})
                                        </button>
                                    </template>
                                </mc-modal>

                                <div v-if="formFieldsInvalidFrom(slot.substituteFormFields)" class="opportunity-appeal-correction-assignment__slot-hint danger__color">
                                    {{ text('select at least one form field') }}
                                </div>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__visibility">
                                <label class="opportunity-appeal-correction-assignment__visibility-item" :for="'sub-show-opinion-' + slot.id">
                                    <input
                                        type="checkbox"
                                        :id="'sub-show-opinion-' + slot.id"
                                        v-model="slot.substituteShowAppealOpinion"
                                        :disabled="slot.substituting">
                                    <span>{{ text('show appeal opinion') }}</span>
                                </label>
                                <label class="opportunity-appeal-correction-assignment__visibility-item" :for="'sub-show-valuer-' + slot.id">
                                    <input
                                        type="checkbox"
                                        :id="'sub-show-valuer-' + slot.id"
                                        v-model="slot.substituteShowOriginalValuerName"
                                        :disabled="slot.substituting">
                                    <span>{{ text('show original valuer name') }}</span>
                                </label>
                            </div>

                            <div class="opportunity-appeal-correction-assignment__substitution-actions">
                                <button
                                    class="button button--sm button--primary"
                                    :class="{ 'disabled': !canConfirmSubstitution(slot) }"
                                    :disabled="!canConfirmSubstitution(slot)"
                                    @click="confirmSubstitution(slot)">

                                    {{ slot.substituting ? text('substituting') : text('confirm substitution') }}
                                </button>

                                <button
                                    class="button button--sm button--text"
                                    :class="{ 'disabled': slot.substituting }"
                                    :disabled="slot.substituting"
                                    @click="cancelSubstitution(slot)">

                                    <?= i::__('Cancelar') ?>
                                </button>
                            </div>
                        </div>
                    </template>

                    <span v-else class="opportunity-appeal-correction-assignment__status-detail--empty">
                        {{ text('no designation') }}
                    </span>
                </div>
            </div>
        </div>
    </template>

    <template #actions="{ close }">
        <button class="button button--text" @click="close()">
            <?= i::__('Cancelar') ?>
        </button>

        <button
            class="button button--md button--primary"
            :class="{ 'disabled': !canSave }"
            :disabled="!canSave"
            @click="saveDesignations(close)">

            {{ saving ? text('saving') : text('save') }}
        </button>
    </template>
</mc-modal>
