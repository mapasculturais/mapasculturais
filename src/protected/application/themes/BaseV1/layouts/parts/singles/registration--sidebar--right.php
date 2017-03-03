<?php 
use MapasCulturais\i;

$configuration = $opportunity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$evaluationMethod = $definition->evaluationMethod;
$evaluation_form_part_name = $evaluationMethod->getEvaluationFormPartName();
?>
<div class="sidebar registration sidebar-right">
    <?php if($action === 'single'): ?>
    
        <?php if($entity->canUser('evaluate')): ?>
        <div id="registration-evaluation-form" class="evaluation-form evaluation-form--<?php echo $evaluationMethod->getSlug(); ?>">
            <form>
                <?php $this->part($evaluation_form_part_name, ['opportunity' => $opportunity, 'entity' => $entity, 'evaluationMethod' => $evaluationMethod]); ?>
                <hr>
                <div style="text-align: right;">
                    <button class="btn btn-default"><?php i::_e('Salvar e Continuar'); ?></button> <br>
                    <button class="btn btn-primary"><?php i::_e('Salvar e Avançar'); ?></button> 
                </div>
            </form>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>