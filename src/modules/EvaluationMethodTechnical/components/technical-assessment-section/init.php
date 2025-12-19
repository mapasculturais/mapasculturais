<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;

$has_evaluations_started= false;
if($committee = $entity->getEvaluationCommittee()){
    foreach($committee as $relation){
        if($relation->metadata['summary']['sent'] > 0){
            $has_evaluations_started = true;
            break;
        }
    }
}

$fieldsDict = [
    'sections' => [
        'name' => [
            'label' => 'fieldSectionName',
            'isRequired' => true
        ],
    ],
    'criteria' => [
        'title' => [
            'label' => 'fieldCriterionTitle',
            'isRequired' => true

        ],
        'max' => [
            'label' => 'fieldCriterionMax',
            'isRequired' => true

        ],
        'weight' => [
            'label' => 'fieldCriterionWeight',
            'isRequired' => true

        ],
    ]
];

$this->jsObject['config']['technicalAssessmentsection'] = [
    'fieldsDict' => $fieldsDict,
    'hasEvaluationsStarted' => $has_evaluations_started
];
