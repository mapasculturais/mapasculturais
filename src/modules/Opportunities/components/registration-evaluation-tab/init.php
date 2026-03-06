<?php

use MapasCulturais\i;

$requestedEntity = $this->controller->requestedEntity;
$firstPhaseOpp = $requestedEntity->firstPhase->opportunity;
$phases = $firstPhaseOpp->phases;
$phase_valuers = [];
$phase_evaluations = [];
$registration_number = $requestedEntity->number;

$addPhaseValuers = function ($phaseOrEmc, $opportunity, &$phase_valuers, &$phase_evaluations) use ($app, $registration_number) {
    $relatedAgents = $phaseOrEmc->relatedAgents ?? [];
    $oppId = (int) ($opportunity->id ?? $opportunity);
    $phase_valuers[$oppId] = [];
    $phase_evaluations[$oppId] = [];

    $opportunityEntity = ($opportunity instanceof \MapasCulturais\Entities\Opportunity)
        ? $opportunity
        : $app->repo('Opportunity')->find($oppId);
    if (!$opportunityEntity) {
        return;
    }

    $registration = $app->repo('Registration')->findOneBy([
        'opportunity' => $opportunityEntity,
        'number' => $registration_number
    ]);

    foreach ($relatedAgents as $group => $agents) {
        foreach ($agents as $agent) {
            $statuses = [
                i::__('Avaliação iniciada'),
                i::__('Avaliação concluída'),
                i::__('Avaliação enviada'),
            ];

            $userId = (int) ($agent->user->id ?? $agent->user ?? 0);
            $user = ($agent->user instanceof \MapasCulturais\Entities\User)
                ? $agent->user
                : $app->repo('User')->find($userId);
            if (!$user) {
                continue;
            }
            if ($registration && ($evaluation = $registration->getUserEvaluation($user))) {
                $status = $statuses[$evaluation->status];
                $result_string = $evaluation->resultString;
                $evaluation_id = $evaluation->id;
                $evaluation_status = $evaluation->status;
                $evaluation_data = $evaluation->simplify('id,result,evaluationData,registration,user,status,createTimestamp,updateTimestamp');
            } else {
                $status = i::__('Avaliação pendente');
                $result_string = '';
                $evaluation_id = null;
                $evaluation_status = null;
                $evaluation_data = null;
            }

            $agentData = method_exists($agent, 'simplify') ? $agent->simplify() : (object) [
                'id' => $agent->id ?? $user->profile->id ?? null,
                'name' => $agent->name ?? $user->profile->name ?? '',
            ];
            $phase_valuers[$oppId][$user->id] = $agentData;
            $phase_evaluations[$oppId][$user->id] = [
                'status' => $status,
                'resultString' => $result_string,
                'id' => $evaluation_id,
                'statusNumber' => $evaluation_status,
                'evaluation' => $evaluation_data,
            ];
        }
    }
};

foreach ($phases as $phase) {
    if ($phase->{'@entityType'} == 'evaluationmethodconfiguration') {
        $opp = $phase->opportunity;
        $addPhaseValuers($phase, $opp, $phase_valuers, $phase_evaluations);

        // Fase de recurso: não está na cadeia nextPhase; é acessada por opportunity->appealPhase
        $appealOpp = $opp->appealPhase ?? null;
        if ($appealOpp && ($appealEmc = $appealOpp->evaluationMethodConfiguration ?? null)) {
            $addPhaseValuers($appealEmc, $appealOpp, $phase_valuers, $phase_evaluations);
        }
    }
}

$this->jsObject['config']['registrationEvaluationTab'] = [
    'valuers' => $phase_valuers,
    'evaluations' => $phase_evaluations,
];