<?php

use MapasCulturais\i;

$this->import('mc-alert mc-confirm-button mc-icon');
?>

<section class="col-12 grid-12 section appeal-technical-score-correction" aria-labelledby="appeal-score-correction-title">
    <h3 id="appeal-score-correction-title" class="col-12"><?= i::__('Revisão da nota técnica') ?></h3>

    <div class="section__content col-12">
        <div class="card owner grid-12">
            <p v-if="loading" class="col-12" role="status"><?= i::__('Carregando dados da correção...') ?></p>
            <mc-alert v-if="error" class="col-12" type="danger" role="alert">{{ error }}</mc-alert>
            <mc-alert v-if="conflict" class="col-12" type="warning" role="alert">
                <?= i::__('Os dados foram atualizados devido a uma alteração concorrente. Revise o caso antes de continuar.') ?>
            </mc-alert>

            <template v-if="!loading && context">
                <div v-if="context.canManageRelator" class="field col-12">
                    <label class="field__label" for="appeal-correction-relator"><?= i::__('Avaliador responsável pela correção') ?></label>
                    <div class="grid-12">
                        <select id="appeal-correction-relator" v-model="selectedRelatorId" class="col-8" :disabled="submitting || hasFinalCorrection">
                            <option value=""><?= i::__('Selecione um avaliador distribuído') ?></option>
                            <option v-for="valuer in context.availableRelators" :key="valuer.id" :value="valuer.id">
                                {{ valuer.name }}
                            </option>
                        </select>
                        <button type="button" class="button button--primary-outline col-4" :disabled="submitting || !selectedRelatorId || hasFinalCorrection" @click="saveRelator">
                            <?= i::__('Definir avaliador') ?>
                        </button>
                    </div>
                    <small><?= i::__('Somente o avaliador designado poderá propor e aplicar mudanças de nota.') ?></small>
                </div>

                <mc-alert v-if="!context.relatorUserId" class="col-12" type="warning">
                    <?= i::__('Defina o avaliador para habilitar a revisão da nota.') ?>
                </mc-alert>

                <form v-if="canEditCorrection" class="col-12 grid-12" @submit.prevent="saveDraft">
                    <div class="field col-12">
                        <label class="field__label" for="appeal-correction-reason"><?= i::__('Justificativa da decisão') ?></label>
                        <textarea id="appeal-correction-reason" v-model="reason" required rows="4" maxlength="5000"></textarea>
                        <small><?= i::__('A justificativa será preservada no histórico interno de auditoria.') ?></small>
                    </div>

                    <fieldset v-for="evaluation in context.evaluations" :key="evaluation.id" class="field col-12">
                        <legend>
                            <label>
                                <input type="checkbox" v-model="selections[evaluation.id].selected">
                                <strong>{{ evaluation.valuer.name }}</strong>
                                <span v-if="evaluation.isTiebreaker"> — <?= i::__('voto de minerva') ?></span>
                            </label>
                        </legend>
                        <p><?= i::__('Resultado atual:') ?> <strong>{{ evaluation.result }}</strong></p>

                        <div v-for="criterion in context.criteria" :key="criterion.id" class="grid-12 field">
                            <label class="col-7">
                                <input
                                    type="checkbox"
                                    v-model="selections[evaluation.id].criteria[criterion.id].selected"
                                    :disabled="!selections[evaluation.id].selected"
                                >
                                {{ criterion.title }}
                            </label>
                            <span class="col-2" :aria-label="'Nota atual ' + evaluation.evaluationData[criterion.id]">
                                {{ evaluation.evaluationData[criterion.id] }} →
                            </span>
                            <input
                                class="col-3"
                                type="number"
                                step="0.1"
                                :min="criterion.min"
                                :max="criterion.max"
                                v-model.number="selections[evaluation.id].criteria[criterion.id].value"
                                :disabled="!selections[evaluation.id].selected || !selections[evaluation.id].criteria[criterion.id].selected"
                                :aria-label="'Nova nota para ' + criterion.title"
                            >
                        </div>
                    </fieldset>

                    <mc-alert v-if="preview" class="col-12" type="info" aria-live="polite">
                        <?= i::__('Prévia do resultado consolidado:') ?> <strong>{{ preview.consolidatedResult }}</strong>
                        · <?= i::__('Pontuação final com bônus:') ?> <strong>{{ preview.score }}</strong>
                        · <?= i::__('Elegível:') ?> <strong>{{ preview.eligible ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}</strong>
                    </mc-alert>

                    <div class="col-12 grid-12">
                        <button type="submit" class="button button--primary-outline col-6" :disabled="submitting">
                            <?= i::__('Salvar proposta e calcular prévia') ?>
                        </button>
                        <mc-confirm-button class="col-6" @confirm="resolveCorrection">
                            <template #button="modal">
                                <button type="button" class="button button--primary" :disabled="submitting || !context.current" @click="modal.open()">
                                    <?= i::__('Deferir recurso e aplicar correção') ?>
                                </button>
                            </template>
                            <template #message>
                                <?= i::__('Confirma o deferimento do recurso e a substituição imediata das notas selecionadas? O histórico anterior será preservado.') ?>
                            </template>
                        </mc-confirm-button>
                    </div>

                    <div class="field col-12">
                        <label>
                            <input type="checkbox" v-model="confirmNoScoreChange">
                            <?= i::__('Confirmo que o recurso será deferido sem alteração da nota técnica') ?>
                        </label>
                        <button
                            v-if="confirmNoScoreChange"
                            type="button"
                            class="button button--primary-outline"
                            :disabled="submitting"
                            @click="resolveCorrection"
                        >
                            <?= i::__('Deferir sem alterar nota') ?>
                        </button>
                    </div>
                </form>

                <div v-if="hasFinalCorrection && context.canManageRelator" class="field col-12">
                    <button type="button" class="button button--primary-outline" :disabled="submitting" @click="reopenCorrection">
                        <?= i::__('Reabrir correção em nova sequência') ?>
                    </button>
                </div>

                <div v-if="history.length" class="col-12">
                    <h4><?= i::__('Histórico interno de auditoria') ?></h4>
                    <details v-for="entry in history" :key="entry.id" class="field">
                        <summary>
                            <?= i::__('Sequência') ?> {{ entry.sequence }} — {{ statusLabel(entry.status) }} — {{ entry.relator.name }}
                        </summary>
                        <p><strong><?= i::__('Justificativa:') ?></strong> {{ entry.reason }}</p>
                        <p v-if="entry.before">
                            <?= i::__('Resultado consolidado:') ?> {{ entry.before.consolidatedResult }} → {{ entry.after.consolidatedResult }}
                        </p>
                        <ul v-if="entry.items?.length">
                            <li v-for="item in entry.items" :key="item.id">
                                {{ item.valuer.name }}:
                                <span v-for="(change, criterionId) in item.changedCriteria" :key="criterionId">
                                    {{ criterionLabel(entry, criterionId) }} ({{ change.before }} → {{ change.after }})
                                </span>
                            </li>
                        </ul>
                    </details>
                </div>
            </template>
        </div>
    </div>
</section>
