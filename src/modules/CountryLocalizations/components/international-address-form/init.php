<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */


$country_code = $app->config['address.defaultCountryCode'] != 'BR' ? $app->config['address.defaultCountryCode'] : null;
$international_localization = $country_code ? $app->getRegisteredCountryLocalizationByCountryCode($country_code) : null;

$default_active_levels = $app->config['address.defaultActiveLevels'];
$default_level_labels = $app->config['address.defaultLevelsLabels'];

$active_levels = [];
foreach ($default_active_levels as $level) {
    if (isset($default_level_labels[$level])) {
        $active_levels[$level] = $default_level_labels[$level];
    }
}

$app->view->jsObject['config']['internationalAddressForm']['activeLevels'] = $international_localization ? $international_localization->activeLevels : $active_levels;
$app->view->jsObject['config']['internationalAddressForm']['levelHierarchy'] = $international_localization ? $international_localization->levelHierarchy : null;