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
            <?php if ($infos) : ?>
                <div id="documentary-evaluation-info" class="alert info">
                    <div class="close" style="cursor: pointer;"></div>
                    <?php if ($part_name = $evaluationMethod->getEvaluationFormInfoPartName()) : ?>
                        <?php $this->part($part_name, $params); ?>
                    <?php endif; ?>
                    <br>
                </div>
            <?php endif; ?>
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
        <?php elseif ($entity->canUser('viewUserEvaluation')) : ?>
            <?php $this->part($evaluation_view_part_name, $params); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>