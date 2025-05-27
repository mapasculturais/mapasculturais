<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$additional_validate_fields = ['workplan', 'goal', 'delivery', 'projectDuration', 'culturalArtisticSegment'];
$additional_validate_fields_steps = [];

$app->applyHookBoundTo($this, 'component(registration-actions).additionalValidateFields', [&$additional_validate_fields, &$additional_validate_fields_steps, $entity]);

$this->jsObject['config']['registrationActions'] = [
    'autosaveDebounce' => $app->config['registration.autosaveTimeout'],
    'additionalValidateFields' => $additional_validate_fields,
    'additionalValidateFieldsSteps' => $additional_validate_fields_steps,
];
