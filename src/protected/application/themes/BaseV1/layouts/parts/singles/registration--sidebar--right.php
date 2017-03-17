<?php
use MapasCulturais\i;

$configuration = $opportunity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$evaluationMethod = $definition->evaluationMethod;
$evaluation_form_part_name = $evaluationMethod->getEvaluationFormPartName();

$params = ['opportunity' => $opportunity, 'entity' => $entity, 'evaluationMethod' => $evaluationMethod];

$infos = (array) $configuration->infos;
?>
<div class="sidebar registration sidebar-right">
    <?php if($action === 'single'): ?>

        <?php if($entity->canUser('evaluate')): ?>
            <style>
            .evaluation-form {
                position:fixed;
                width:22%;
            }
            @media screen and (max-width: 1366px) and (min-width: 1200px) {
                .evaluation-form {
                    width:18%;
                }
            }
            </style>
        <div id="registration-evaluation-form" class="evaluation-form evaluation-form--<?php echo $evaluationMethod->getSlug(); ?>">
            <div id="documentary-evaluation-info" class="alert info">
                <div class="close" style="cursor: pointer;"></div>
                <?php if($part_name = $evaluationMethod->getEvaluationFormInfoPartName()): ?>
                    <?php $this->part($part_name, $params); ?>
                <?php endif; ?>

                <?php if($infos && isset($infos['general'])): ?>
                    <hr>
                    <strong><?php i::_e('Informações gerais') ?></strong>
                    <p><?php echo $infos['general'] ?></p>
                <?php endif; ?>

                <?php if($infos && $entity->category && isset($infos[$entity->category])): ?>
                    <hr>
                    <strong><?php echo $entity->category ?></strong>
                    <p><?php echo $infos[$entity->category] ?></p>
                <?php endif; ?>

            </div>
            <form>
                <?php $this->part($evaluation_form_part_name, $params); ?>
                <hr>
                <div style="text-align: right;">
                    <button class="btn btn-primary js-evaluation-submit js-next"><?php i::_e('Finalizar Avaliação e Avançar'); ?> &gt;&gt;</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
