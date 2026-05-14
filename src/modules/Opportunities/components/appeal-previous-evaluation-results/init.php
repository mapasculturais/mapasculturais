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

if (in_array($entity->showPreviousPhaseEvaluationDetails, [false, 0, '0'], true)) {
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

foreach ($sent_evaluations as $evaluation) {
    $detail = $evaluation_method->getEvaluationDetails($evaluation);

    if ($evaluation_configuration->publishValuerNames) {
        $detail['valuer'] = $evaluation->user->profile->simplify('id,name,singleUrl');
    }

    $registration_data['evaluationsDetails'][] = $detail;
}

$this->jsObject['config']['registrationResults']['shouldDisplayEvaluationResults'][$parent_registration->id] = true;
$this->jsObject['config']['appealPreviousEvaluationResults'] = [
    'registration' => $registration_data,
    'phase' => $evaluation_configuration->jsonSerialize(),
];
