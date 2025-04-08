<?php

use MapasCulturais\i;

$requestedEntity = $this->controller->requestedEntity;
$phases = $requestedEntity->opportunity->phases;
$phase_valuers = [];
$phase_evaluations = [];
$registration_number = $requestedEntity->number;

foreach ($phases as $phase) {
    if ($phase->{'@entityType'} == 'evaluationmethodconfiguration') {
        $phase_valuers[$phase->opportunity->id] = [];
        $phase_evaluations[$phase->opportunity->id] = [];

        $registration = $app->repo('Registration')->findOneBy([
            'opportunity' => $phase->opportunity,
            'number' => $registration_number
        ]);

        foreach ($phase->relatedAgents as $group => $agents) {
            foreach ($agents as $agent) {
                $statuses = [
                    i::__('Avaliação iniciada'),
                    i::__('Avaliação concluída'),
                    i::__('Avaliação enviada'),
                ];

                if ($registration && ($evaluation = $registration->getUserEvaluation($agent->user))) {
                    $status = $statuses[$evaluation->status];
                    $result_string = $evaluation->resultString;
                } else {
                    $status = i::__('Avaliação pendente');
                    $result_string = '';
                }

                $phase_valuers[$phase->opportunity->id][$agent->user->id] = $agent->simplify();
                $phase_evaluations[$phase->opportunity->id][$agent->user->id] = [
                    'status' => $status,
                    'resultString' => $result_string,
                ];
            }
        }
    }
}

$this->jsObject['config']['registrationEvaluationTab'] = [
    'valuers' => $phase_valuers,
    'evaluations' => $phase_evaluations,
];