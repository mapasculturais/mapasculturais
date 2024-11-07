<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$autosaveTimeoutInMilliseconds = $app->config['registration.autosaveTimeout'];

$autosaveTimeoutInCentiseconds = $autosaveTimeoutInMilliseconds / 1000;

$this->jsObject['config']['registrationAutosaveNotification'] = [
    'autosaveDebounce' => $autosaveTimeoutInCentiseconds
];


