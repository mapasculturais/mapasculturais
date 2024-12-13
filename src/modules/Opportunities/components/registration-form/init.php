<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

/** @var MapasCulturais\Entities\Opportunity $opportunity */
$opportunity = $this->controller->requestedEntity->opportunity;
$this->jsObject['config']['registrationForm'] = [
    'fields' => $opportunity->registrationFieldConfigurations,
    'files' => $opportunity->registrationFileConfigurations,
];