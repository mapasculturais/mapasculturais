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
    'fieldsDict' => $fieldsDict
];
