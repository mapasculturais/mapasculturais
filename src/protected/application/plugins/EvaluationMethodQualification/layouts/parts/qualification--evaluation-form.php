<?php

namespace EvaluationMethodQualification;

use MapasCulturais\i;

$params = ['registration' => $entity, 'opportunity' => $opportunity];
?>
<?php $this->applyTemplateHook('evaluationForm.qualification', 'before', $params); ?>
<div id="qualification-evaluation-form" class="qualification-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.qualification', 'begin', $params); ?>
    <div id="qualification-evaluation-form--container"></div>
    <?php $this->applyTemplateHook('evaluationForm.qualification', 'end', $params); ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.qualification', 'after', $params); ?>

<script id='qualification-evaluation-form-template' class='js-mustache-template' type="html/template">
    <?php $this->applyTemplateHook('evaluationForm.qualification.template', 'before', $params); ?>
    <div id="evaluatin-field-{{id}}" class="qualification-evaluation-form--field">
        <?php $this->applyTemplateHook('evaluationForm.qualification.template', 'begin', $params); ?>
        <h6>{{label}}</h6>
        <div>
            <select name="data[{{id}}][qualification]">
                <option>Opção 1</option>
                <option>Opção 2</option>
            </select>
        </div>

        {{olegario}}
        
        <label class="textarea-label">
            <?php i::_e('Justificativa / Observações') ?><br>
            <textarea name="data[{{id}}][obs]">{{obs}}</textarea>
        </label>
        <?php $this->applyTemplateHook('evaluationForm.qualification.template', 'end', $params); ?>
    </div>
    <?php $this->applyTemplateHook('evaluationForm.qualification.template', 'after', $params); ?>
</script>