<?php 
namespace EvaluationMethodDocumentary; 

use MapasCulturais\i;

$evaluation = $entity->getUserEvaluation();

$data = $evaluation->evaluationData;
//eval(\psy\sh());
$class = $evaluation->result ? 'evaluation-valid' : 'evaluation-invalid'
?>
<div id="documentary-evaluation-view" class="widget documentary-evaluation-view <?php echo $class ?>">
    
    <h3><?php i::_e('Avaliação Documental') ?></h3>
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
                <?php if($d['obs']): ?>
                    <p><?php echo nl2br($d['obs']) ?></p>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</div>