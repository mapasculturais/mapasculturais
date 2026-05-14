<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Registration;

$entity = $this->controller->requestedEntity;

if (!($entity instanceof Registration) || !$entity->opportunity->isAppealPhase) {
    return;
}

if (!$entity->canUser('viewUserEvaluation') && !$entity->canUser('@control')) {
    return;
}

if (in_array($entity->opportunity->showPreviousPhaseEvaluationDetails, [false, 0, '0'], true)) {
    return;
}

$parent_opportunity = $entity->opportunity->parent;

if (!$parent_opportunity || !$parent_opportunity->evaluationMethodConfiguration) {
    return;
}

$parent_registration = $app->repo('Registration')->findOneBy([
    'owner' => $entity->owner->id,
    'opportunity' => $parent_opportunity->id,
    'number' => $entity->number,
]);

if (!$parent_registration || !$parent_registration->evaluationMethod) {
    return;
}

$evaluation_method = $parent_registration->evaluationMethod;
$evaluation_configuration = $parent_registration->opportunity->evaluationMethodConfiguration;
$sent_evaluations = $parent_registration->sentEvaluations;

if (count($sent_evaluations) === 0) {
    return;
}

$registration_data = (array) $parent_registration->jsonSerialize();

if (!$registration_data) {
    return;
}

try {
    $registration_data['consolidatedDetails'] = $evaluation_method->getConsolidatedDetails($parent_registration);
} catch (\Throwable $e) {
    $registration_data['consolidatedDetails'] = [
        'sentEvaluationCount' => count($sent_evaluations),
    ];
}

$registration_data['evaluationsDetails'] = [];

$current_user = $app->user;
$is_appeal_evaluator = false;

$appeal_evaluation_config = $entity->opportunity->evaluationMethodConfiguration;
if ($appeal_evaluation_config) {
    $valuer_user_ids = $appeal_evaluation_config->getValuerUserIds();
    $is_appeal_evaluator = in_array($current_user->id, $valuer_user_ids);
}

$can_view_valuer_names = $entity->opportunity->canUser('@control') || 
                          $is_appeal_evaluator || 
                          $evaluation_configuration->publishValuerNames;

foreach ($sent_evaluations as $evaluation) {
    $detail = $evaluation_method->getEvaluationDetails($evaluation);

    if ($can_view_valuer_names) {
        $detail['valuer'] = $evaluation->user->profile->simplify('id,name,singleUrl');
    }

    $registration_data['evaluationsDetails'][] = $detail;
}

$this->jsObject['config']['registrationResults']['shouldDisplayEvaluationResults'][$parent_registration->id] = true;
$this->jsObject['config']['appealPreviousEvaluationResults'] = [
    'registration' => $registration_data,
    'phase' => $evaluation_configuration->jsonSerialize(),
];
