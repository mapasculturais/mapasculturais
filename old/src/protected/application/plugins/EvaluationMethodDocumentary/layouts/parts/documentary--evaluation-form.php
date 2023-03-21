<?php
namespace EvaluationMethodDocumentary;

use MapasCulturais\i;

$params = ['registration' => $entity, 'opportunity' => $opportunity];
?>
<?php $this->applyTemplateHook('evaluationForm.documentary', 'before', $params); ?>
<div id="documentary-evaluation-form" class="documentary-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.documentary', 'begin', $params); ?>
    <div id="documentary-evaluation-form--container"></div>
    <?php $this->applyTemplateHook('evaluationForm.documentary', 'end', $params); ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.documentary', 'after', $params); ?>

<script id='documentary-evaluation-form-template' class='js-mustache-template' type="html/template">
    <?php $this->applyTemplateHook('evaluationForm.documentary.template', 'before', $params); ?>
    <div id="evaluatin-field-{{id}}" class="documentary-evaluation-form--field">
        <?php $this->applyTemplateHook('evaluationForm.documentary.template', 'begin', $params); ?>
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
            <?php i::_e('Descumprimento do(s) item(s) do edital:') ?><br>
            <textarea class="textarea-small" rows="2" name="data[{{id}}][obs_items]">{{obs_items}}</textarea>
        </label>
        <label class="textarea-label">
            <?php i::_e('Justificativa / Observações') ?><br>
            <textarea name="data[{{id}}][obs]">{{obs}}</textarea>
        </label>
        <?php $this->applyTemplateHook('evaluationForm.documentary.template', 'end', $params); ?>
    </div>
    <?php $this->applyTemplateHook('evaluationForm.documentary.template', 'after', $params); ?>
</script>
