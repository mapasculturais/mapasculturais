<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-file
    mc-alert
    mc-card
    mc-icon
    mc-loading
    mc-modal
');
?>

<div class="opportunity-appeal-correction-evaluation">
    <mc-loading v-if="loading" :condition="loading"><?= i::__('carregando...') ?></mc-loading>

    <mc-alert v-else-if="error" type="danger">{{ error.message }}</mc-alert>

    <template v-else>
        <div class="opportunity-appeal-correction-evaluation__workspace">
            <section class="opportunity-appeal-correction-evaluation__registration">
                <mc-card>
                    <template #title>
                        <h3><?= i::__('Formulário da inscrição') ?></h3>
                    </template>
                    <template #content>
                        <p class="opportunity-appeal-correction-evaluation__registration-hint">
                            <?= i::__('Campos liberados pelo gestor na designação. Use-os para refazer a análise antes de corrigir a nota.') ?>
                        </p>

                        <dl class="opportunity-appeal-correction-evaluation__registration-summary">
                            <div class="opportunity-appeal-correction-evaluation__info">
                                <dt><?= i::__('Inscrição') ?></dt>
                                <dd>{{ visibleForm?.number || environment.registrationNumber }}</dd>
                            </div>
                            <div v-if="visibleForm?.category" class="opportunity-appeal-correction-evaluation__info">
                                <dt><?= i::__('Categoria') ?></dt>
                                <dd>{{ visibleForm.category }}</dd>
                            </div>
                            <div v-if="visibleForm?.range" class="opportunity-appeal-correction-evaluation__info">
                                <dt><?= i::__('Faixa') ?></dt>
                                <dd>{{ visibleForm.range }}</dd>
                            </div>
                            <div v-if="visibleForm?.proponentType" class="opportunity-appeal-correction-evaluation__info">
                                <dt><?= i::__('Tipo de proponente') ?></dt>
                                <dd>{{ visibleForm.proponentType }}</dd>
                            </div>
                        </dl>

                        <div v-if="visibleForm?.agentsSummary" class="opportunity-appeal-correction-evaluation__form-block">
                            <h4><?= i::__('Agente proponente') ?></h4>
                            <p class="semibold">
                                <a
                                    v-if="visibleForm.agentsSummary.owner?.singleUrl"
                                    :href="visibleForm.agentsSummary.owner.singleUrl"
                                    target="_blank"
                                    rel="noopener">
                                    {{ registrationOwnerName }}
                                </a>
                                <template v-else>{{ registrationOwnerName || '—' }}</template>
                            </p>
                            <p v-if="registrationOwnerDescription">{{ registrationOwnerDescription }}</p>
                            <p v-if="registrationOwnerAreas">
                                <span class="opportunity-appeal-correction-evaluation__label"><?= i::__('Áreas de atuação') ?>:</span>
                                {{ registrationOwnerAreas }}
                            </p>
                        </div>

                        <div v-if="visibleForm?.spaceSummary?.length" class="opportunity-appeal-correction-evaluation__form-block">
                            <h4><?= i::__('Espaço vinculado') ?></h4>
                            <ul class="opportunity-appeal-correction-evaluation__form-list">
                                <li v-for="space in visibleForm.spaceSummary" :key="space.id">
                                    <a v-if="space.singleUrl" :href="space.singleUrl" target="_blank" rel="noopener">{{ space.name }}</a>
                                    <template v-else>{{ space.name }}</template>
                                </li>
                            </ul>
                        </div>

                        <div v-if="visibleForm?.fields?.length" class="opportunity-appeal-correction-evaluation__form-block">
                            <h4><?= i::__('Campos do formulário') ?></h4>
                            <div
                                v-for="field in visibleForm.fields"
                                :key="field.id"
                                class="opportunity-appeal-correction-evaluation__form-field">
                                <div class="opportunity-appeal-correction-evaluation__form-field-title semibold">
                                    {{ field.title }}
                                    <small v-if="field.phaseName">{{ field.phaseName }}</small>
                                </div>
                                <ul v-if="field.items?.length" class="opportunity-appeal-correction-evaluation__form-list">
                                    <li v-for="(item, index) in field.items" :key="item.id || item.url || index">
                                        <a
                                            v-if="item.url"
                                            :href="item.url"
                                            target="_blank"
                                            rel="noopener">
                                            {{ item.name || item.url }}
                                        </a>
                                        <template v-else>{{ item.name || '—' }}</template>
                                    </li>
                                </ul>
                                <div v-else class="opportunity-appeal-correction-evaluation__form-field-value">
                                    {{ field.value || '—' }}
                                </div>
                            </div>
                        </div>

                        <div v-if="visibleForm?.files?.length" class="opportunity-appeal-correction-evaluation__form-block">
                            <h4><?= i::__('Anexos') ?></h4>
                            <div
                                v-for="group in visibleForm.files"
                                :key="group.id"
                                class="opportunity-appeal-correction-evaluation__form-field">
                                <div class="opportunity-appeal-correction-evaluation__form-field-title semibold">
                                    {{ group.title }}
                                    <small v-if="group.phaseName">{{ group.phaseName }}</small>
                                </div>
                                <ul v-if="group.files?.length" class="opportunity-appeal-correction-evaluation__form-list">
                                    <li v-for="file in group.files" :key="file.id">
                                        <a v-if="file.url" :href="file.url" target="_blank" rel="noopener">{{ file.name }}</a>
                                        <template v-else>{{ file.name }}</template>
                                    </li>
                                </ul>
                                <div v-else class="opportunity-appeal-correction-evaluation__form-field-value">—</div>
                            </div>
                        </div>

                        <mc-alert v-if="!hasVisibleFormContent" type="helper">
                            <?= i::__('Nenhum campo do formulário foi liberado nesta designação.') ?>
                        </mc-alert>
                    </template>
                </mc-card>
            </section>

            <section class="opportunity-appeal-correction-evaluation__correction">
        <mc-card>
            <template #title>
                <h3><?= i::__('Dados da designação') ?></h3>
            </template>
            <template #content>
                <dl class="opportunity-appeal-correction-evaluation__summary">
                    <div class="opportunity-appeal-correction-evaluation__info opportunity-appeal-correction-evaluation__info--wide">
                        <dt><?= i::__('Oportunidade') ?></dt>
                        <dd>{{ environment.opportunityName }}</dd>
                    </div>
                    <div class="opportunity-appeal-correction-evaluation__info">
                        <dt><?= i::__('Inscrição') ?></dt>
                        <dd>{{ environment.registrationNumber }}</dd>
                    </div>
                    <div class="opportunity-appeal-correction-evaluation__info">
                        <dt><?= i::__('Prazo') ?></dt>
                        <dd>{{ formattedDeadline || text('no-deadline') }}</dd>
                    </div>
                    <div v-if="environment.targetEvaluatorName" class="opportunity-appeal-correction-evaluation__info opportunity-appeal-correction-evaluation__info--wide">
                        <dt><?= i::__('Avaliador original') ?></dt>
                        <dd>{{ environment.targetEvaluatorName }}</dd>
                    </div>
                    <div class="opportunity-appeal-correction-evaluation__info">
                        <dt><?= i::__('Status') ?></dt>
                        <dd>
                            <span class="opportunity-appeal-correction-evaluation__badge" :class="'opportunity-appeal-correction-evaluation__badge--' + environment.status">{{ statusLabel }}</span>
                        </dd>
                    </div>
                    <div class="opportunity-appeal-correction-evaluation__info">
                        <dt><?= i::__('Tipo de correção') ?></dt>
                        <dd>
                            <span class="opportunity-appeal-correction-evaluation__badge">{{ correctionTypeLabel }}</span>
                        </dd>
                    </div>
                </dl>
            </template>
        </mc-card>

        <mc-card v-if="committeeRegistration">
            <template #title>
                <h3><?= i::__('Parecer da Comissão de Recursos') ?></h3>
            </template>
            <template #content>
                <!--
                    Mesmo comportamento do registration-results: botão
                    "Exibir detalhamento" abre modal; nome do parecerista
                    só aparece se o payload trouxer valuer (publishValuerNames
                    / @control) — senão só o #sequencial.
                -->
                <div class="registration-results opportunity-appeal-correction-evaluation__committee-opinion">
                    <mc-modal
                        :title="committeeModalTitle"
                        classes="registration-results__modal registration-results__modal--with-chat">
                        <template #default>
                            <div class="registration-results__content">

                                <!-- Técnico: consolidado + cards com pontuação -->
                                <template v-if="committeeOpinionMethod === 'technical'">
                                    <div class="registration-results__card">
                                        <div class="registration-results__card-header">
                                            <div class="registration-results__card-title">
                                                <h4 class="bold"><?= i::__('Resultado Consolidado') ?></h4>
                                            </div>
                                            <div>
                                                <p><label><?= i::__('Pontuação total: ') ?></label> <strong>{{ committeeRegistration.score }}</strong></p>
                                                <p v-if="committeeRegistration.consolidatedDetails?.maxScore">
                                                    <label><?= i::__('Pontuação máxima: ') ?></label>
                                                    <strong>{{ committeeRegistration.consolidatedDetails.maxScore }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-for="(evaluation, index) in committeeRegistration.evaluationsDetails"
                                        :key="evaluation.id || index"
                                        class="registration-results__card">
                                        <div class="registration-results__card-header">
                                            <div class="registration-results__card-title">
                                                <h4
                                                    v-if="evaluation.valuer && evaluation.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ evaluation.committeeSequentialNumber }} - ({{ evaluation.valuer.id }} - {{ evaluation.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="evaluation.valuer"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }} - ({{ evaluation.valuer.id }} - {{ evaluation.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="evaluation.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ evaluation.committeeSequentialNumber }}
                                                </h4>
                                                <h4 v-else class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }}
                                                </h4>
                                                <p v-if="committeeRegistration.consolidatedResult">
                                                    <label><?= i::__('Pontuação total: ') ?></label>
                                                    <strong>{{ committeeRegistration.consolidatedResult }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="registration-results__card-content">
                                            <div class="registration-results__opinion registration-results__opinion--document">
                                                <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
                                                <div class="registration-results__opinion-text">
                                                    <p>{{ evaluation.obs }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="evaluation.scores?.length" class="registration-results__card-content">
                                            <div class="registration-results__opinion registration-results__opinion--document">
                                                <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento da pontuação') ?>:</h5>
                                                <ul>
                                                    <li v-for="section in evaluation.scores" :key="section.id || section.name">
                                                        {{ section.name }}: <strong>{{ section.score }}</strong>
                                                        <em>(<?= i::__('pontuação máxima') ?>: {{ section.maxScore }})</em>
                                                        <ul>
                                                            <li v-for="cri in section.criteria" :key="cri.id">
                                                                {{ cri.title }}: <strong>{{ cri.score }}</strong>
                                                                <em>(<?= i::__('pontuação máxima') ?>: {{ cri.maxScore }})</em>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Documental -->
                                <template v-else-if="committeeOpinionMethod === 'documentary'">
                                    <div
                                        v-for="(detail, index) in committeeRegistration.evaluationsDetails"
                                        :key="detail.id || index"
                                        class="registration-results__card">
                                        <div class="registration-results__card-header">
                                            <div class="registration-results__card-title">
                                                <h4
                                                    v-if="detail.valuer && detail.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ detail.committeeSequentialNumber }} - ({{ detail.valuer.id }} - {{ detail.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="detail.valuer"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }} - ({{ detail.valuer.id }} - {{ detail.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="detail.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ detail.committeeSequentialNumber }}
                                                </h4>
                                                <h4 v-else class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }}
                                                </h4>
                                                <p v-if="detail.evaluationResult != null">
                                                    <label><?= i::__('Resultado final: ') ?></label>
                                                    <strong :class="documentaryStatusColor(detail.evaluationResult)">
                                                        <mc-icon name="circle" :class="documentaryStatusColor(detail.evaluationResult)"></mc-icon>
                                                        {{ documentaryStatusLabel(detail.evaluationResult) }}
                                                    </strong>
                                                </p>
                                            </div>
                                        </div>
                                        <template v-for="(item, itemKey) in detail.evaluations" :key="itemKey">
                                            <div v-if="itemKey !== 'valuer'" class="registration-results__card-content">
                                                <div class="registration-results__opinion registration-results__opinion--document">
                                                    <div class="registration-results__opinion-text">
                                                        <p>
                                                            <mc-icon name="circle" :class="documentaryStatusColor(item.evaluation)"></mc-icon>
                                                            <strong :class="documentaryStatusColor(item.evaluation)">
                                                                {{ documentaryStatusLabel(item.evaluation) }}
                                                            </strong>
                                                        </p>
                                                        <p><strong>{{ item.label }}</strong></p>
                                                        <div v-if="item.obs">
                                                            <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
                                                            <ul><li>{{ item.obs }}</li></ul>
                                                        </div>
                                                        <div v-if="item.obs_items">
                                                            <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento') ?>:</h5>
                                                            <ul><li>{{ item.obs_items }}</li></ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Contínuo / simples / qualificação / fallback -->
                                <template v-else>
                                    <div
                                        v-for="(evaluation, index) in committeeRegistration.evaluationsDetails"
                                        :key="evaluation.id || index"
                                        class="registration-results__card">
                                        <div class="registration-results__card-header">
                                            <div class="registration-results__card-title">
                                                <h4
                                                    v-if="evaluation.valuer && evaluation.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ evaluation.committeeSequentialNumber }} - ({{ evaluation.valuer.id }} - {{ evaluation.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="evaluation.valuer"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }} - ({{ evaluation.valuer.id }} - {{ evaluation.valuer.name }})
                                                </h4>
                                                <h4
                                                    v-else-if="evaluation.committeeSequentialNumber"
                                                    class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ evaluation.committeeSequentialNumber }}
                                                </h4>
                                                <h4 v-else class="registration-results__opinion-title bold">
                                                    <?= i::__('Parecerista: ') ?> #{{ index + 1 }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="registration-results__card-content">
                                            <div class="registration-results__opinion registration-results__opinion--document">
                                                <h5 class="registration-results__opinion-title bold"><?= i::__('Justificativa') ?>:</h5>
                                                <div class="registration-results__opinion-text">
                                                    <p>{{ evaluation.obs }}</p>
                                                </div>
                                                <div v-if="committeeEvaluationEntity(evaluation)" class="registration-results__opinion-attachment">
                                                    <entity-file
                                                        :entity="committeeEvaluationEntity(evaluation)"
                                                        group-name="evaluationAttachment"
                                                        classes="col-12">
                                                    </entity-file>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <template #button="modal">
                            <button
                                type="button"
                                class="button button--primary button--sm button--large"
                                @click="modal.open()">
                                <?= i::__('Exibir detalhamento') ?>
                            </button>
                        </template>
                    </mc-modal>
                </div>
            </template>
        </mc-card>

        <!-- F7 (#51): formulário conforme o método do slot -->
        <mc-card v-if="isTechnical">
            <template #title>
                <h3><?= i::__('Critérios liberados para correção') ?></h3>
            </template>
            <template #content>
                <div class="opportunity-appeal-correction-evaluation__list">
                    <div class="opportunity-appeal-correction-evaluation__item" v-for="criterion in criteriaList" :key="criterion.id">
                        <div class="opportunity-appeal-correction-evaluation__item-header">
                            <h4 class="bold">{{ criterion.title }}</h4>
                            <div class="opportunity-appeal-correction-evaluation__item-meta">
                                <span><?= i::__('Nota máxima') ?>: <strong>{{ criterion.max }}</strong></span>
                                <span><?= i::__('Peso') ?>: <strong>{{ criterion.weight }}</strong></span>
                            </div>
                        </div>

                        <div class="opportunity-appeal-correction-evaluation__compare">
                            <div class="opportunity-appeal-correction-evaluation__original">
                                <span class="opportunity-appeal-correction-evaluation__label"><?= i::__('Nota original') ?></span>
                                <span class="opportunity-appeal-correction-evaluation__value">{{ originalNote(criterion) }}</span>
                            </div>
                            <div class="field">
                                <label :for="'corrected-criterion-' + criterion.id"><?= i::__('Nota corrigida') ?></label>
                                <input
                                    :id="'corrected-criterion-' + criterion.id"
                                    :disabled="isLocked"
                                    :max="criterion.max"
                                    min="0"
                                    step="0.1"
                                    type="number"
                                    v-model.number="formData.data[criterion.id]"
                                    @input="handleInput(criterion)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="opportunity-appeal-correction-evaluation__total">
                    <span><?= i::__('Pontuação total') ?></span>
                    <strong>{{ totalScore }}</strong>
                </div>

                <div v-if="originalOpinion" class="opportunity-appeal-correction-evaluation__form-block">
                    <h4><?= i::__('Parecer técnico original') ?></h4>
                    <p class="opportunity-appeal-correction-evaluation__original-opinion">{{ originalOpinion }}</p>
                </div>

                <div class="field opportunity-appeal-correction-evaluation__opinion">
                    <label for="corrected-technical-obs"><?= i::__('Parecer da correção (adendo)') ?></label>
                    <small class="opportunity-appeal-correction-evaluation__opinion-hint">
                        <?= i::__('Explique o que motivou a nova nota. Este texto fica registrado junto à correção.') ?>
                    </small>
                    <textarea
                        id="corrected-technical-obs"
                        name="corrected-technical-obs"
                        :disabled="isLocked"
                        rows="5"
                        v-model="formData.data.obs"></textarea>
                </div>
            </template>
        </mc-card>

        <mc-card v-else-if="isDocumentary">
            <template #title>
                <h3><?= i::__('Campos liberados para correção') ?></h3>
            </template>
            <template #content>
                <div class="opportunity-appeal-correction-evaluation__list">
                    <div class="opportunity-appeal-correction-evaluation__item" v-for="field in fieldsList" :key="field.id">
                        <div class="opportunity-appeal-correction-evaluation__item-header">
                            <h4 class="bold">{{ field.title }}</h4>
                        </div>

                        <div class="opportunity-appeal-correction-evaluation__compare">
                            <div class="opportunity-appeal-correction-evaluation__original">
                                <span class="opportunity-appeal-correction-evaluation__label"><?= i::__('Avaliação original') ?></span>
                                <span class="opportunity-appeal-correction-evaluation__value">{{ originalFieldEvaluation(field) }}</span>
                            </div>
                            <div class="field">
                                <label><?= i::__('Avaliação corrigida') ?></label>
                                <div class="opportunity-appeal-correction-evaluation__field-options">
                                    <label class="field__checkbox">
                                        <input type="radio" value="" v-model="formData.data[field.id].evaluation" :disabled="isLocked" :name="'corrected-field-' + field.id">
                                        <span><?= i::__('Não avaliada') ?></span>
                                    </label>
                                    <label class="field__checkbox">
                                        <input type="radio" value="valid" v-model="formData.data[field.id].evaluation" :disabled="isLocked" :name="'corrected-field-' + field.id">
                                        <span><?= i::__('Válida') ?></span>
                                    </label>
                                    <label class="field__checkbox">
                                        <input type="radio" value="invalid" v-model="formData.data[field.id].evaluation" :disabled="isLocked" :name="'corrected-field-' + field.id">
                                        <span><?= i::__('Inválida') ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label :for="'corrected-field-obs-' + field.id"><?= i::__('Observações do campo') ?></label>
                            <textarea
                                :id="'corrected-field-obs-' + field.id"
                                :name="'corrected-field-obs-' + field.id"
                                :disabled="isLocked"
                                v-model="formData.data[field.id].obs"></textarea>
                        </div>
                    </div>
                </div>
            </template>
        </mc-card>

        <mc-card v-else-if="isSimple">
            <template #title>
                <h3><?= i::__('Correção do resultado') ?></h3>
            </template>
            <template #content>
                <div class="opportunity-appeal-correction-evaluation__list">
                    <mc-alert type="helper"><?= i::__('Método de avaliação sem critérios: a correção libera o resultado integral da avaliação.') ?></mc-alert>

                    <div class="opportunity-appeal-correction-evaluation__item">
                        <div class="opportunity-appeal-correction-evaluation__compare">
                            <div class="opportunity-appeal-correction-evaluation__original">
                                <span class="opportunity-appeal-correction-evaluation__label"><?= i::__('Status original') ?></span>
                                <span class="opportunity-appeal-correction-evaluation__value">{{ originalStatus() }}</span>
                            </div>
                            <div class="field">
                                <label for="corrected-global-status"><?= i::__('Status corrigido') ?></label>
                                <select
                                    id="corrected-global-status"
                                    name="corrected-global-status"
                                    v-model="formData.data.status"
                                    :disabled="isLocked">

                                    <option value="" disabled><?= i::__('Selecione o status') ?></option>
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label for="corrected-global-obs"><?= i::__('Observações') ?></label>
                            <textarea
                                id="corrected-global-obs"
                                name="corrected-global-obs"
                                :disabled="isLocked"
                                v-model="formData.data.obs"></textarea>
                        </div>
                    </div>
                </div>
            </template>
        </mc-card>

        <mc-alert v-if="isLocked" type="warning"><?= i::__('Correção enviada, edição bloqueada.') ?></mc-alert>

        <div class="opportunity-appeal-correction-evaluation__actions" v-if="!isLocked">
            <button class="button button--primary-outline" :class="{'disabled': savingDraft}" @click="saveDraft()">
                <?= i::__('Salvar rascunho') ?>
            </button>

            <mc-modal title="<?= i::__('Enviar correção') ?>">
                <template #default>
                    <p><?= i::__('Ao confirmar, a correção será aplicada à avaliação e a edição será bloqueada. Esta ação não pode ser desfeita.') ?></p>
                </template>

                <template #actions="modal">
                    <button class="button button--text" @click="modal.close()"><?= i::__('Cancelar') ?></button>
                    <button class="button button--primary" :class="{'disabled': sending}" @click="sendCorrection(); modal.close()">
                        <?= i::__('Confirmar envio') ?>
                    </button>
                </template>

                <template #button="modal">
                    <button class="button button--primary" @click="modal.open()">
                        <?= i::__('Enviar definitivamente') ?>
                    </button>
                </template>
            </mc-modal>
        </div>
            </section>
        </div>
    </template>
</div>
