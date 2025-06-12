<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$app->view->enqueueScript('components', 'countries', 'js/countries.js', ['components-init']);

$country_field_enabled = $app->config['address.countryFieldEnabled'];
$country_default_code = $app->config['address.defaultCountryCode'];


$app->view->jsObject['config']['countryLocalization'] = [
    'countryFieldEnabled' => $country_field_enabled,
    'countryDefaultCode' => $country_default_code
];