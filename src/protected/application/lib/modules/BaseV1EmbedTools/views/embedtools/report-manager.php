<?php

$app->view->enqueueStyle('app', 'reports', 'css/reports.css');
$app->view->enqueueScript('app', 'reports', 'js/ng.reports.js', ['entity.module.opportunity']);
$app->view->jsObject['angularAppDependencies'][] = 'ng.reports';

$module = $app->modules['Reports'];

$statusValue = 'all';

switch ($statusValue) {
    case 'all':
        $status = '> 0';
        break;
    case 'draft':
        $status = '= 0';
        break;
    case 'approved':
        $status = '= 10';
        break;
    default:
        $status = '> 0';
        break;
}

$_SESSION['reportStatusRegistration'] = $status;

$app->view->jsObject['reportStatus'] = $statusValue;

$opportunity = $this->controller->requestedEntity;
$sendHook = [];

if (!$opportunity->isOpportunityPhase) {
    if ($registrationsByTime = $module->registrationsByTime($opportunity, $status)) {
        $sendHook['registrationsByTime'] = $registrationsByTime;
    }
}

if ($registrationsByStatus = $module->registrationsByStatus($opportunity)) {
    $sendHook['registrationsByStatus'] = $registrationsByStatus;
}

if ($opportunity->evaluationMethod->slug == 'technical') {
    if ($registrationsByEvaluation = $module->registrationsByEvaluationStatusBar($opportunity)) {
        $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
    }
} else {
    if ($registrationsByEvaluation = $module->registrationsByEvaluation($opportunity, $statusValue)) {
        $sendHook['registrationsByEvaluation'] = $registrationsByEvaluation;
    }
}

if ($registrationsByCategory = $module->registrationsByCategory($opportunity)) {
    $sendHook['registrationsByCategory'] = $registrationsByCategory;
}

$sendHook['opportunity'] = $opportunity;

$sendHook['self'] = $module;
$sendHook['statusRegistration'] = $statusValue;

if ($opportunity->canUser('@control') && $module->hasRegistrations($opportunity)) {
    $this->part('opportunity-reports', $sendHook);
}
