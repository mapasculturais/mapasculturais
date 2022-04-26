<?php 
use MapasCulturais\i;

$configuration = $entity->evaluationMethodConfiguration;
$definition = $configuration->definition;
?>

<div id="evaluations-config" class="aba-content" ng-controller="EvaluationMethodConfigurationController">

    <?php
    if(is_object($definition) && property_exists($definition, 'evaluationMethod') ) :
        $evaluationMethod = $definition->evaluationMethod;
        $config_form_part_name = $evaluationMethod->getConfigurationFormPartName();
    ?>
        <p class="js-editable"><?php echo $definition->name ?> - <em><?php echo $definition->description ?></em></p>

        <?php $this->part('singles/opportunity-evaluations--committee', ['entity' => $entity]) ?>

        <?php if($config_form_part_name): ?>
            <div> <?php $this->part($config_form_part_name, ['entity' => $entity]) ?> </div> <hr>
        <?php endif; ?>

        <div>
            <h4> <?php i::_e('Textos informativos para a fichas de inscrição') ?> </h4>
            <div class="evaluations-config--intro">
                <label> <?php i::_e('Para todas as inscrições') ?> <br>
                    <textarea ng-model="config['infos']['general']" ng-model-options="{ debounce: 1000, updateOn: 'blur' }"></textarea>
                </label>
            </div>

            <h4> <?php i::_e('Por categoria') ?> </h4>
            <div ng-repeat="category in data.categories" class="evaluations-config--intro">
                <label> {{category}} <br>
                    <textarea ng-model="config['infos'][category]" ng-model-options="{ debounce: 1000, updateOn: 'blur' }"></textarea>
                </label>
            </div>
        </div>

    <?php
    else:
        i::_e('As inscrições para esta oportunidade já foram encerradas. Não é mais possível configurar a avaliação.');
    endif; ?>

    <?php if($entity->canUser('@control')):?>
        
    <?php $this->part('singles/opportunity-evaluations-fields--config', ['entity' => $entity]) ?>

    <?php endif; ?>

</div>