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

    <label class="opportunity-reports-chart-form__field">
        <span><?= i::__('Título') ?></span>
        <input type="text" v-model="title" required />
    </label>

    <label class="opportunity-reports-chart-form__field">
        <span><?= i::__('Descrição') ?></span>
        <textarea v-model="description"></textarea>
    </label>

    <label class="opportunity-reports-chart-form__field">
        <span><?= i::__('Tipo de gráfico') ?></span>
        <select v-model="typeGraphic">
            <option value="pie"><?= i::__('Pizza') ?></option>
            <option value="bar"><?= i::__('Barra') ?></option>
            <option value="horizontalBar"><?= i::__('Barra horizontal') ?></option>
            <option value="line"><?= i::__('Linha') ?></option>
            <option value="table"><?= i::__('Tabela') ?></option>
        </select>
    </label>

    <label class="opportunity-reports-chart-form__field">
        <span><?= i::__('Campo (eixo A)') ?></span>
        <select v-model="fieldAIndex">
            <option value="" disabled><?= i::__('Selecione') ?></option>
            <option v-for="(field, index) in availableFields" :key="index" :value="index">{{ field.label }}</option>
        </select>
    </label>

    <label v-if="typeGraphic !== 'pie'" class="opportunity-reports-chart-form__field">
        <span><?= i::__('Campo (eixo B)') ?></span>
        <select v-model="fieldBIndex">
            <option value=""><?= i::__('Nenhum') ?></option>
            <option v-for="(field, index) in availableFields" :key="index" :value="index">{{ field.label }}</option>
        </select>
    </label>

    <label v-if="typeGraphic === 'bar' || typeGraphic === 'horizontalBar'" class="opportunity-reports-chart-form__field opportunity-reports-chart-form__field--checkbox">
        <input type="checkbox" v-model="groupData" />
        <span><?= i::__('Empilhar barras') ?></span>
    </label>

    <div class="opportunity-reports-chart-form__preview">
        <mc-loading :condition="loadingPreview"></mc-loading>
        <opportunity-reports-chart-card v-if="preview" title="<?= i::esc_attr__('Pré-visualização') ?>" :type="typeGraphic" :chart="preview"></opportunity-reports-chart-card>
    </div>

    <div class="opportunity-reports-chart-form__actions">
        <button type="button" class="button button--outline" @click="$emit('cancelled')"><?= i::__('Cancelar') ?></button>
        <button type="submit" class="button" :disabled="saving"><?= i::__('Salvar') ?></button>
    </div>
</form>
