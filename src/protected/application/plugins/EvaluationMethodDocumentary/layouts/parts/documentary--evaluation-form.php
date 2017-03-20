<?php 
namespace EvaluationMethodDocumentary; 

use MapasCulturais\i;

?>
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
    .evaluation-invalid,.evaluation-invalid:hover {
        background-color: rgba(255,200,200,.5) !important;
    }
    .evaluation-valid,.evaluation-valid:hover {
        background-color: rgba(200,255,200,.5) !important;
    }
    .evaluation-empty {

    }
    </style>
    
    <div id="documentary-evaluation-form--container"></div>
</div>
<script id='documentary-evaluation-form-template' class='js-mustache-template' type="html/template">
    <div id="evaluatin-field-{{id}}" class="documentary-evaluation-form--field">
        <h6>{{label}}</h6>
        <p>
            <label class="input-label">
                <input type="radio" name="data[{{id}}][evaluation]" {{#empty}}checked="checked"{{/empty}} value="">
                <em><?php i::_e('Não avaliar')?></em>
            </label><br>
            <label class="input-label">
                <input type="radio" name="data[{{id}}][evaluation]" {{#valid}}checked="checked"{{/valid}} value="<?php echo STATUS_VALID ?>">
                <em><?php i::_e('Válida')?></em>
            </label>
            <label class="input-label">
                <input type="radio" name="data[{{id}}][evaluation]" {{#invalid}}checked="checked"{{/invalid}} value="<?php echo STATUS_INVALID ?>">
                <em><?php i::_e('Inválida')?></em>
            </label>
        </p>
        <input type="hidden" name="data[{{id}}][label]" value="{{label}}">
        <label class="textarea-label">
            <?php i::_e('Justificativa / Observações') ?><br>
            <textarea name="data[{{id}}][obs]">{{obs}}</textarea>
        </label>
    </div>
</script>
