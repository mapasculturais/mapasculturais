<?php
use MapasCulturais\i;

$opportunity = $entity->opportunity;
$configuration = $opportunity->evaluationMethodConfiguration;
$definition = $configuration->definition;
$evaluationMethod = $definition->evaluationMethod;
$evaluation_form_part_name = $evaluationMethod->getEvaluationFormPartName();
$evaluation_view_part_name = $evaluationMethod->getEvaluationViewPartName();

$params = ['opportunity' => $opportunity, 'entity' => $entity, 'evaluationMethod' => $evaluationMethod, 'plugin' => $entity->evaluationMethod];

$evaluation = $this->getCurrentRegistrationEvaluation($entity);

$infos = (array) $configuration->infos;

$evaluationAgent = false;

foreach ($opportunity->getEvaluationCommittee() as $evaluation_user) {
    if ($evaluation_user->agent === \MapasCulturais\App::i()->user->profile) {
        $evaluationAgent = true;
    }
}
$action = 'single';

?>
<?php if ($action === 'single' && $entity->canUser('viewUserEvaluation')) : ?>
    <div id="registration-evaluation-form">
        <?php if ($evaluationAgent && $entity->canUser('evaluate') && (!$evaluation || $evaluation->status <= 0)) : ?>
            <form>
                <?php if ($evaluation) : ?>
                    <div>
                        <strong><?php i::_e('Avaliador') ?>:</strong> <?php echo $evaluation->user->profile->name ?>
                        <input type="hidden" name="uid" value="<?php echo $evaluation->user->id; ?>" />
                    </div>
                <?php endif ?>
                <?php $this->part($evaluation_form_part_name, $params); ?>
            </form>
            <hr>
            <div style="text-align: right;">
                <button class="btn btn-primary js-evaluation-submit js-next"><?php i::_e('Finalizar Avaliação e Avançar'); ?> &gt;&gt;</button>
            </div>
        <?php else: ?>
            <?php if (new DateTime('now') > $entity->opportunity->evaluationMethodConfiguration->evaluationTo) : ?>
                <strong><?= i::__('O período de avaliação se encerrou em ') . $entity->opportunity->evaluationMethodConfiguration->evaluationTo->format(i::__("d/m/Y à\s H:i"))?> </strong>
            <?php endif; ?>
            <?php if ($entity->canUser('viewUserEvaluation')) : ?>
                <?php $this->part($evaluation_view_part_name, $params); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>