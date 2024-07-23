<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$fieldsDict = [
    'sections' => [
        'name' => [
            'label' => 'fieldSectionName',
            'isRequired' => true
        ],
    ],
    'criteria' => [
        'name' => [
            'label' => 'fieldCriterionName',
            'isRequired' => true

        ],
        'weight' => [
            'label' => 'fieldCriterionWeight',
            'isRequired' => false
        ],
    ]
];

$this->jsObject['config']['qualificationAssessmentSection'] = [
    'fieldsDict' => $fieldsDict
];
