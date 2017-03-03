<?php 
use MapasCulturais\i;

$configuration = $entity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$evaluationMethod = $definition->evaluationMethod;
$config_form_part_name = $evaluationMethod->getConfigurationFormPartName();
?>
<div id="evaluations-config" class="aba-content">
    
    <p class="js-editable"><?php echo $definition->name ?> - <em><?php echo $definition->description ?></em></p>
    
    <?php $this->part('singles/opportunity-evaluations--committee', ['entity' => $entity]) ?>
    
    <?php if($config_form_part_name): ?>
        <?php $this->part($config_form_part_name, ['entity' => $entity]) ?>
    <?php endif; ?>
</div>