<?php 
namespace EvaluationMethodDocumentary; 

use MapasCulturais\i;

$evaluation = $this->getCurrentRegistrationEvaluation($entity);

if(!$evaluation){
    return;
}

$data = $evaluation->evaluationData;

$class = $evaluation->result == 1 ? 'evaluation-valid' : 'evaluation-invalid';
?>
<h3><?php i::_e('Avaliação Documental'); ?>:</h3> 
<div id="documentary-evaluation-view" class="widget documentary-evaluation-view <?php echo $class ?>">
    <h4><?php echo $evaluation->resultString; ?></h4>
    
    <ul>
    <?php foreach($data as $d): ?>
        <?php if($d['evaluation']): ?>
            <li>
                <label><?php echo $d['label']; ?></label>: 
                <?php if($d['evaluation'] == 'valid'): ?>
                    <strong><?php i::_e('Válida') ?></strong>
                <?php else: ?>
                    <strong><?php i::_e('Inválida') ?></strong>
                <?php endif; ?>
                <?php if($d['obs_items']): ?>
                    <label><?php i::_e('Descumprimento do(s) item(s) do edital:') ?></label><br>
                    <p><?php echo nl2br($d['obs_items']) ?></p>
                <?php endif; ?>
                <?php if($d['obs']): ?>
                    <label><?php i::_e('Justificativa / Observações') ?></label><br>
                    <p><?php echo nl2br($d['obs']) ?></p>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</div>