<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;

$sections = $entity->opportunity->evaluationMethodConfiguration->sections;
$criteria = $entity->opportunity->evaluationMethodConfiguration->criteria;
$data = [];

$statusList = [
    ['value' => '2', 'label' => i::__('Inválida')],
    ['value' => '3', 'label' => i::__('Não selecionada')],
    ['value' => '8', 'label' => i::__('Suplente')],
    ['value' => '10', 'label' => i::__('Selecionada')],
];

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
                'description' => isset($crit->description) ? $crit->description : '',
                'options' => isset($crit->options) ? $crit->options : [],
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
    'sections' => $data,
    'statusList' => $statusList,
];