<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$opportunity = $this->controller->requestedEntity;
$config = $opportunity->evaluationMethodConfiguration ?? null;

$has_evaluations_started = $config ? $config->hasStartedEvaluations() : false;
$is_admin = $app->user->is('admin');
$can_delete_criteria_and_sections = $is_admin || !$has_evaluations_started;

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
    'hasEvaluationsStarted' => $has_evaluations_started,
    'isAdmin' => $is_admin,
    'canDeleteCriteriaAndSections' => $can_delete_criteria_and_sections,
];
