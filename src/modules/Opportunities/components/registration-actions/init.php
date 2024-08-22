<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->jsObject['config']['registrationActions'] = [
    'autosaveDebounce' => $app->config['registration.autosaveTimeout']
];
