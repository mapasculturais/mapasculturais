<?php
use MapasCulturais\i;

$configuration = $opportunity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$evaluationMethod = $definition->evaluationMethod;
$evaluation_form_part_name = $evaluationMethod->getEvaluationFormPartName();
$evaluation_view_part_name = $evaluationMethod->getEvaluationViewPartName();

$params = ['opportunity' => $opportunity, 'entity' => $entity, 'evaluationMethod' => $evaluationMethod];

$evaluation = $this->getCurrentRegistrationEvaluation($entity);

$infos = (array) $configuration->infos;

$evaluationAgent = false;
foreach ($opportunity->getEvaluationCommittee() as $evaluation_user) {
    if($evaluation_user->agent === \MapasCulturais\App::i()->user->profile)
        $evaluationAgent = true;
}
?>
<?= $this->applyTemplateHook('registration-sidebar-rigth','before')?>
<div class="sidebar registration sidebar-right">
    <?= $this->applyTemplateHook('registration-sidebar-rigth','begin')?>
    <?php if($action === 'single' && $entity->canUser('viewUserEvaluation')): ?>
        <div id="registration-evaluation-form" class="evaluation-form evaluation-form--<?php echo $evaluationMethod->getSlug(); ?>">
            <?php if($evaluationAgent && $entity->canUser('evaluate')): ?>
                <?php if($infos): ?>
                    <div id="documentary-evaluation-info" class="alert info">
                        <div class="close" style="cursor: pointer;"></div>
                        <?php if($part_name = $evaluationMethod->getEvaluationFormInfoPartName()): ?>
                            <?php $this->part($part_name, $params); ?>
                        <?php endif; ?>


                        <?php if($infos && $entity->category && isset($infos[$entity->category])): ?>
                            <hr>
                            <strong><?php echo $entity->category ?></strong>
                            <p><?php echo $infos[$entity->category] ?></p>
                        <?php endif; ?>

                        <?php if($infos && isset($infos['general'])): ?>
                            <hr>
                            <strong><?php i::_e('Informações gerais') ?></strong>
                            <p><?php echo $infos['general'] ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <form>
                <?php if($opportunity->canUser('@control') && $evaluation): ?>
                <div>
                    <strong><?php i::_e('Avaliador')?>:</strong> <?php echo $evaluation->user->profile->name ?>
                    <input type="hidden" name="uid" value="<?php echo $evaluation->user->id; ?>" />
                </div>
                <?php endif; ?>
                <?php $this->part($evaluation_form_part_name, $params); ?>
                <hr>
                <div style="text-align: right;">
                    <button class="btn btn-primary js-evaluation-submit js-next"><?php i::_e('Finalizar Avaliação e Avançar'); ?> &gt;&gt;</button>
                </div>
            </form>
            <?php elseif($entity->canUser('viewUserEvaluation')): ?>
                <?php $this->part($evaluation_view_part_name, $params); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?= $this->applyTemplateHook('registration-sidebar-rigth','end') ?>
</div>
<?= $this->applyTemplateHook('registration-sidebar-rigth','after'); ?>