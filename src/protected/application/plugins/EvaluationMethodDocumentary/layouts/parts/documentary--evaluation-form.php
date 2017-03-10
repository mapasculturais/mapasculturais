<div id="documentary-evaluation-form" class="documentary-evaluation-form">
    <style>
    .documentary-evaluation-form--field {
        font-size: 0.75rem;
        display:none;
    }
    .documentary-evaluation-form--field label {
        margin-bottom: 1em;
    }
    .documentary-evaluation-form--field label.input-label {
        font-size: 1.25em;
        margin-right: 1em;
    }

    .documentary-evaluation-form--field textarea {
        width:100%;
        height:250px;
    }
    .field-shadow {
        box-shadow: 0px 0px 30px 0;
    }
    .evaluation-invalid {
        background-color: rgba(255,200,200,.5);
    }
    .evaluation-empty {

    }
    </style>
    <div id="documentary-evaluation-info" class="alert info">
        <p><?php MapasCulturais\i::_e('Para <strong>validar</strong> a inscrição salve a avaliação sem invalidar nenhum campo.') ?></p>
        <p><?php MapasCulturais\i::_e('Para <strong>invalidar</strong> a inscrição, clique no(s) campo(s) inválido(s), marque o checkbox <strong>Marcar como inválido</strong> e preencha a justificativa. Após invalidar todos os campos inválidos, salve a avaliação.') ?></p>

        <div class="close" style="cursor: pointer;"></div>
    </div>
    <div id="documentary-evaluation-form--container"></div>
</div>
<script id='documentary-evaluation-form-template' class='js-mustache-template' type="html/template">
    <div id="evaluatin-field-{{id}}" class="documentary-evaluation-form--field">
        <h6>{{label}}</h6>
        <p>
            <label class="input-label">
                <input type="checkbox" name="data[{{id}}][invalid]" {{#invalid}}checked="checked"{{/invalid}} value="1">
                <em><?php MapasCulturais\i::_e('Marcar como inválido')?></em>
            </label>
        </p>
        <input type="hidden" name="data[{{id}}][label]" value="{{label}}">
        <label class="textarea-label">
            <?php MapasCulturais\i::_e('Justificativa / Observações') ?><br>
            <textarea name="data[{{id}}][obs]">{{obs}}</textarea>
        </label>
    </div>
</script>
