<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
    opportunity-reports-chart-card
');
?>
<form class="opportunity-reports-chart-form" @submit.prevent="submit()">
    <p v-if="error" class="opportunity-reports-chart-form__error">{{ error }}</p>

    <div v-if="step === 1" class="opportunity-reports-chart-form__step opportunity-reports-chart-form__step--type">
        <p><?= i::__('Antes de definir os parâmetros, defina o tipo de gráfico que você deseja:') ?></p>
        <p class="opportunity-reports-chart-form__step-title"><b><?= i::__('Tipo de visualização') ?></b></p>

        <div class="opportunity-reports-chart-form__type-options">
            <label class="opportunity-reports-chart-form__type-option">
                <input type="radio" v-model="typeGraphic" value="pie" />
                <i class="fas fa-chart-pie"></i>
                <span><b><?= i::__('Gráfico de pizza') ?></b></span>
            </label>

            <label class="opportunity-reports-chart-form__type-option">
                <input type="radio" v-model="typeGraphic" value="line" />
                <i class="fas fa-chart-area"></i>
                <span><b><?= i::__('Gráfico de linha') ?></b></span>
            </label>

            <label class="opportunity-reports-chart-form__type-option">
                <input type="radio" v-model="typeGraphic" value="bar" />
                <i class="far fa-chart-bar"></i>
                <span><b><?= i::__('Gráfico de coluna') ?></b></span>
            </label>

            <label class="opportunity-reports-chart-form__type-option">
                <input type="radio" v-model="typeGraphic" value="horizontalBar" />
                <i class="fas fa-bars"></i>
                <span><b><?= i::__('Gráfico de barra') ?></b></span>
            </label>

            <label class="opportunity-reports-chart-form__type-option">
                <input type="radio" v-model="typeGraphic" value="table" />
                <i class="fas fa-th-list"></i>
                <span><b><?= i::__('Gráfico de tabela') ?></b></span>
            </label>
        </div>

        <label v-if="showGroupData" class="opportunity-reports-chart-form__field opportunity-reports-chart-form__field--checkbox">
            <input type="checkbox" v-model="groupData" />
            <span><b><?= i::__('Agrupar dados') ?></b></span>
        </label>
    </div>

    <div v-if="step === 2" class="opportunity-reports-chart-form__step opportunity-reports-chart-form__step--data">
        <p><?= i::__('Agora defina o título e dados exibido no gráfico') ?></p>

        <div class="opportunity-reports-chart-form__row">
            <label class="opportunity-reports-chart-form__field">
                <span><?= i::__('Título do gráfico') ?></span>
                <input type="text" v-model="title" placeholder="<?= i::esc_attr__('Digite um título que represente os dados do novo gráfico') ?>" />
            </label>

            <label class="opportunity-reports-chart-form__field">
                <span><?= i::__('Breve descrição') ?></span>
                <input type="text" v-model="description" placeholder="<?= i::esc_attr__('Digite uma descrição resumida') ?>" />
            </label>
        </div>

        <div class="opportunity-reports-chart-form__row">
            <label class="opportunity-reports-chart-form__field">
                <span v-if="typeGraphic === 'table' || typeGraphic === 'horizontalBar'"><?= i::__('Dados a serem exibidos na linha') ?></span>
                <span v-else><?= i::__('Dados a serem exibidos') ?></span>
                <select v-model="fieldAIndex">
                    <option value="" disabled><?= i::__('Selecione uma opção ...') ?></option>
                    <option v-for="(field, index) in availableFields" :key="index" :value="index">{{ field.label }}</option>
                </select>
            </label>

            <label v-if="showFieldB" class="opportunity-reports-chart-form__field">
                <span v-if="typeGraphic === 'table' || typeGraphic === 'horizontalBar'"><?= i::__('Dados a serem exibidos na coluna') ?></span>
                <span v-else><?= i::__('Dados a serem exibidos') ?></span>
                <select v-model="fieldBIndex">
                    <option value="" disabled><?= i::__('Selecione uma opção ...') ?></option>
                    <option v-for="(field, index) in availableFields" :key="index" :value="index">{{ field.label }}</option>
                </select>
            </label>
        </div>

        <div class="opportunity-reports-chart-form__preview">
            <mc-loading :condition="loadingPreview"></mc-loading>
            <opportunity-reports-chart-card v-if="preview" title="<?= i::esc_attr__('Pré-visualização') ?>" :type="typeGraphic" :chart="preview"></opportunity-reports-chart-card>
        </div>
    </div>

    <div class="opportunity-reports-chart-form__actions">
        <button type="button" class="button button--outline" @click="$emit('cancelled')"><?= i::__('Cancelar') ?></button>
        <button v-if="step === 2" type="button" class="button button--outline" @click="goToStep1()"><?= i::__('Voltar') ?></button>
        <button v-if="step === 1" type="button" class="button" :disabled="!typeGraphic" @click="goToStep2()"><?= i::__('Próxima etapa') ?></button>
        <button v-if="step === 2" type="submit" class="button" :disabled="saving || !canSubmit"><?= i::__('Gerar gráfico') ?></button>
    </div>
</form>
