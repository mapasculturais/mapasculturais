<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Registration;

$entity = $this->controller->requestedEntity;
$class = $entity->getClassName();

if($class == Registration::class) {
    $opportunity = $registration->opportunity;
    $evaluation_configuration = $opportunity->evaluationMethodConfiguration;
    
    $status_names = $opportunity->statusLabels ?: $evaluation_configuration->defaultStatuses;
    
    $this->jsObject['config']['registrationStatus'] = [
        'statuses' => $status_names,
    ];
}