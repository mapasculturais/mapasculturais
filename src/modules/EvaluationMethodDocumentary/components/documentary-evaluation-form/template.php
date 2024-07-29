<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<div v-if="enableForm" id="evaluation-form">
    <h3>{{ fieldLabel }}</h3>
    <input type="hidden" v-model="fieldLabel" />
    <div>
        <label>
            <input type="radio" value="nao-avaliar" v-model="evaluation" />
            Não avaliar
        </label>
        <label>
            <input type="radio" value="valid" v-model="evaluation" />
            Válida
        </label>
        <label>
            <input type="radio" value="invalid" v-model="evaluation" />
            Inválida
        </label>
    </div>

    <label>
        Descumprimento do(s) item(s) do edital:
        <textarea v-model="obsItems"></textarea>
    </label>

    <label>
        Justificativa / Observações
        <textarea v-model="obs"></textarea>
    </label>
</div>
