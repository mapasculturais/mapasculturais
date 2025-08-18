<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$default_active_levels = $app->config['address.defaultActiveLevels'];
$default_level_labels = $app->config['address.defaultLevelsLabels'];

$active_levels = [];
foreach ($default_active_levels as $level) {
    if (isset($default_level_labels[$level])) {
        $active_levels[$level] = $default_level_labels[$level];
    }
}

$app->view->jsObject['config']['entityAddressFormInternacional']['activeLevels'] = $active_levels;