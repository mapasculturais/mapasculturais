<?php 
use MapasCulturais\i;

$configuration = $entity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$propertiesMetadata = $configuration->getPropertiesMetadata();
$this->jsObject['entity']['definition']['evaluationFrom']  = $propertiesMetadata['evaluationFrom'];
$this->jsObject['entity']['definition']['evaluationTo']  = $propertiesMetadata['evaluationTo'];

?>

<div id="evaluations-config" class="aba-content" ng-controller="EvaluationMethodConfigurationController">

    <div class="highlighted-message clearfix">
        <div class="registration-dates clear">
            <?php /* Translators: "de" como início de um intervalo de data *DE* 25/1 a 25/2 às 13:00 */ ?>
            <?php \MapasCulturais\i::_e("Avaliações abertas de");?>
            <strong class="js-editable" data-type="date" data-yearrange="2000:+25" data-viewformat="dd/mm/yyyy" data-edit="evaluationFrom" <?php echo $configuration->evaluationFrom ? "data-value='" . $configuration->evaluationFrom->format('Y-m-d') . "'" : ' '?> data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data inicial");?>"><?php echo $configuration->evaluationFrom ? $configuration->evaluationFrom->format('d/m/Y') : \MapasCulturais\i::__("Data inicial"); ?></strong>
            <?php /* Translators: "a" indicando intervalo de data de 25/1 *A* 25/2 às 13:00 */ ?>
            <?php \MapasCulturais\i::_e("a");?>
            <strong class="js-editable" data-type="date" data-yearrange="2000:+25" data-viewformat="dd/mm/yyyy" data-edit="evaluationTo" <?php echo $configuration->evaluationTo ? "data-value='".$configuration->evaluationTo->format('Y-m-d') . "'" : ''?> data-timepicker="#evaluationTo_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data final");?>"><?php echo $configuration->evaluationTo ? $configuration->evaluationTo->format('d/m/Y') : \MapasCulturais\i::__("Data final"); ?></strong>
            <?php /* Translators: "às" indicando horário de data de 25/1 a 25/2 *ÀS* 13:00 */ ?>
            <?php \MapasCulturais\i::_e("às");?>
            <strong class="js-editable" id="evaluationTo_time" data-datetime-value="<?php echo $configuration->evaluationTo ? $configuration->evaluationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e("Hora final");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Hora final");?>"><?php echo $configuration->evaluationTo ? $configuration->evaluationTo->format('H:i') : ''; ?></strong>
        </div>
    </div>
    
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