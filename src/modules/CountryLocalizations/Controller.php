<?php

namespace CountryLocalizations;

use MapasCulturais\App;
use MapasCulturais\Traits;

class Controller extends \MapasCulturais\Controller
{   

    use Traits\ControllerAPI;

    function API_findSublevels(){
        $app = App::i();
        $default_country_code = $app->config['address.defaultCountryCode'];
        $localization = $app->getRegisteredCountryLocalizationByCountryCode($default_country_code);
        $hierarchy = $localization->levelHierarchy;

        $requested_level = (int) ($this->data['level'] ?? null);
        $filters = array_diff_key($this->data, ['level' => '']) ?? [];

        if (!$requested_level) {
            $this->errorJson(true);
        }

        $hierarchy = $hierarchy[1] ?? [];
        $result = $this->findSublevels($hierarchy, 2, [], $requested_level, $filters);
        $this->json($result);
    }

    function findSublevels($hierarchy, $current_level, $trail, $target_level, $filters) {
        $result = [];

        if (!is_array($hierarchy)) {
            return $result;
        }

        foreach ($hierarchy as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_string($value[0]) && !isset($value[1])) {
                $label = $value[0];
                $code = $label;

                // Atualiza trilha com nível anterior
                $trail["level:$current_level"] = $trail["level:$current_level"] ?? ['code' => 'unknown', 'label' => 'unknown'];

                $filter_level = $this->getLastFilterInTrail($filters, $trail);

                $result[] = [
                    'level' => $target_level,
                    'label' => $label,
                    'code' => $code,
                    $filter_level => $trail[$filter_level] ?? null
                ];
                continue;
            }

            $label = is_array($value) ? ($value[0] ?? $key) : $value;
            $code = is_string($key) ? $key : $label;

            $trail["level:$current_level"] = [
                'code' => $code,
                'label' => $label
            ];

            // Aplica filtro se existir para esse nível
            if (isset($filters["level:$current_level"])) {
                $allowed = (array) $filters["level:$current_level"];
                if (!in_array($code, $allowed)) {
                    continue;
                }
            }

            // Se for o nível desejado
            if ($current_level == $target_level) {
                $filter_level = $this->getLastFilterInTrail($filters, $trail);

                $result[] = [
                    'level' => $target_level,
                    'label' => $label,
                    'code' => $code,
                    $filter_level => $trail[$filter_level] ?? null
                ];
            }

            if (is_array($value) && isset($value[1])) {
                $children = $this->findSublevels($value[1], $current_level + 1, $trail, $target_level, $filters);
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }
    

    function getLastFilterInTrail($filters, $trail) {
        $filteredLevels = array_keys($filters);
        rsort($filteredLevels); 

        foreach ($filteredLevels as $levelKey) {
            if (isset($trail[$levelKey])) {
                return $levelKey;
            }
        }

        return 'level:0';
    }

}
