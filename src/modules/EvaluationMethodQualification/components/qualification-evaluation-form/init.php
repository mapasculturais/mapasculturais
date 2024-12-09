<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections ?: [];
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria;
$data = [];

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $user = $app->repo("User")->find($this->controller->data['user']);
}else{
    $user = $app->user;
}

foreach ($sections as $section) {
    $sectionData = [
        'id' => $section->id,
        'name' => $section->name,
        'criteria' => [],
        'status' => i::__('Não avaliada')
    ];
    
    foreach ($criteria as $crit) {
        if ($crit->sid === $section->id) {
            
            $critStatus = isset($crit->status) ? $crit->status : i::__('Não avaliada');
            
            $sectionData['criteria'][] = [
                'id' => $crit->id,
                'sid' => $crit->sid,
                'name' => $crit->name,
                'description' => $crit->description ?? '',
                'options' => $crit->options ?? [],
                'notApplyOption' => $crit->notApplyOption,
                'status' => $critStatus
            ];
            
            if ($critStatus === 'avaliada') {
                $sectionData['status'] = i::__('avaliada');
            } elseif ($critStatus === 'suplente' && $sectionData['status'] !== i::__('avaliada')) {
                $sectionData['status'] = i::__('suplente');
            }
        }
    }
    
    $data[] = $sectionData;
}

$this->jsObject['config']['qualificationEvaluationForm'] = [
    'evaluationData' => $entity->getUserEvaluation($user),
    'sections' => $data,
];