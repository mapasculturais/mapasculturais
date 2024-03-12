<?php

use MapasCulturais\i;

$app->view->enqueueStyle('app', 'reports', 'css/reports.css');
$app->view->enqueueScript('app', 'reports', 'js/ng.reports.js', ['entity.module.opportunity']);
$app->view->jsObject['angularAppDependencies'][] = 'ng.reports';

$module = $app->modules['Reports'];

$statusValue = $this->controller->urlData['status'] ?? 'all';

switch ($statusValue) {
    case 'all':
        $status = '> 0';
        break;
    case 'draft':
        $status = '= 0';
        break;
    case 'invalid': 
        $status = '= 2';
        break;
    case 'notapproved': 
        $status = '= 3';
        break;
    case 'waitlist': 
        $status = '= 8';
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

if ($opportunity->evaluationMethod && $opportunity->evaluationMethod->slug == 'technical') {
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
$sendHook['hidePrintButton'] = true;

if ($opportunity->canUser('@control') && $module->hasRegistrations($opportunity)) { 
    
    $phase_name = '';
    $num = 0;
    foreach($opportunity->phases as $phase) {
        $num++;
        if ($phase->{'@entityType'} == 'opportunity' && $phase->id == $opportunity->id) {
            if ($opportunity->evaluationMethodConfiguration) {
                $n2 = $num + 1;
                $phase_name = "{$num}º {$opportunity->name} / {$n2}º {$opportunity->evaluationMethodConfiguration->name}";
            } else {
                $phase_name = "{$num}º {$opportunity->name}";
            }
            break;
        }
    }
    ?>
    <button class="btn btn-default print-reports" onclick="window.print();"><i class="fas fa-print"></i> <?php i::_e("Imprimir");?></button>
    <header class="print-only print-header">
        <h1 class="print-header__title"><?= $opportunity->name ?></h1>
        <p><?= $phase_name ?></p>
    </header>
    <?php
    $this->part('opportunity-reports', $sendHook);
}