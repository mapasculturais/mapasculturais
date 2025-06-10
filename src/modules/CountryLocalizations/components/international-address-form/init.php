<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */


$country_code = $app->config['address.defaultCountryCode'] != 'BR' ? $app->config['address.defaultCountryCode'] : null;
$international_localization = $country_code ? $app->getRegisteredCountryLocalizationByCountryCode($country_code) : null;

$app->view->jsObject['config']['internationalAddressForm']['activeLevels'] = $international_localization ? $international_localization->activeLevels : $app->config['address.defaultActiveLevels'];
$app->view->jsObject['config']['internationalAddressForm']['levelHierarchy'] = $international_localization ? $international_localization->levelHierarchy : null;