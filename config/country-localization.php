<?php

use MapasCulturais\i;

$default_levels_labels = json_encode([
  1 => i::__('Região'),
  2 => i::__('Estado/Província'),
  3 => i::__('Mesorregião/Subdivisão'),
  4 => i::__('Município/Cidade/Comune'),
  5 => i::__('Distrito/Setor'),
  6 => i::__('Bairro'),
]);

$default_active_levels = json_encode([1, 2, 4, 6]);

return [
  'address.countryFieldEnabled' => env('ADDRESS_COUNTRY_FIELD_ENABLED', true),
  'address.defaultCountryCode' => env('ADDRESS_DEFAULT_COUNTRY_CODE', 'BR'),
  'address.defaultLevelsLabels' => (array) json_decode(env('ADDRESS_DEFAULT_LEVELS_LABELS', $default_levels_labels), true),
  'address.defaultActiveLevels' => (array) json_decode(env('ADDRESS_DEFAULT_ACTIVE_LEVELS', $default_active_levels), true),
];
