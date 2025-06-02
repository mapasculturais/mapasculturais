<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */


$br_localization = $app->getRegisteredCountryLocalizationByCountryCode('BR');
$app->view->jsObject['config']['brasilAddressForm']['statesAndCities'] = $br_localization->levelHierarchy;
$app->view->jsObject['config']['brasilAddressForm']['statesAndCitiesCountryCode'] = $br_localization->countryCode;