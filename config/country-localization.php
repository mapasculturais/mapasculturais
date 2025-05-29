<?php

use MapasCulturais\i;

$default_active_levels = json_encode([
    1 => i::__('Região'),
  	2 => i::__('Estado/Província'),
  	4 => i::__('Município/Cidade/Comune'),
  	6 => i::__('Bairro')
]);

return [
    'address.countryFieldEnabled' => env('ADDRESS_COUNTRY_FIELD_ENABLED', false),
    'address.defaultCountryCode' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'BR'),
    'address.defaultActiveLevels' => (array) json_decode(env('ADDRESS_DEFAULT_ACTIVE_LEVELS', $default_active_levels), true),
];